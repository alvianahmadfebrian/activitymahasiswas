<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class AiChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $sessions = ChatSession::where('user_id', $user->id)
            ->latest()
            ->get();

        $currentSession = null;
        $messages = collect();

        return view('chatbot.index', compact(
            'sessions',
            'currentSession',
            'messages'
        ));
    }

    public function newChat()
    {
        return redirect()->route('chatbot.index');
    }

    public function new()
    {
        return $this->newChat();
    }

    public function show($sessionId)
    {
        $user = Auth::user();

        $sessions = ChatSession::where('user_id', $user->id)
            ->latest()
            ->get();

        $currentSession = ChatSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $messages = ChatMessage::where('chat_session_id', (string) $currentSession->id)
            ->where('user_id', $user->id)
            ->oldest()
            ->get();

        return view('chatbot.index', compact(
            'sessions',
            'currentSession',
            'messages'
        ));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'chat_session_id' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $user = Auth::user();
        $userMessage = trim((string) $request->input('message', ''));
        $uploadedFile = $request->file('file');

        if ($userMessage === '' && !$uploadedFile) {
            return response()->json([
                'success' => false,
                'reply' => 'Pesan atau file tidak boleh kosong.',
            ], 422);
        }

        $session = $this->getOrCreateSession($request, $userMessage, $uploadedFile);

        $userFileData = null;

        if ($uploadedFile) {
            $userFileData = $this->storeUserFile($uploadedFile);
        }

        ChatMessage::create([
            'user_id' => $user->id,
            'chat_session_id' => (string) $session->id,
            'role' => 'user',
            'message' => $userMessage !== '' ? $userMessage : 'Saya mengupload lampiran.',
            'file_url' => $userFileData['url'] ?? null,
            'file_path' => $userFileData['path'] ?? null,
            'file_type' => $userFileData['type'] ?? null,
            'file_name' => $userFileData['name'] ?? null,
        ]);

        // Jalankan Agentic Loop menggunakan Groq API
        $agentResult = $this->runAgent($session, $userMessage, $userFileData);

        $aiReply = $agentResult['reply'];
        $fileData = $agentResult['file_data'];
        $redirectUrl = $agentResult['redirect_url'];

        $assistantMessage = ChatMessage::create([
            'user_id' => $user->id,
            'chat_session_id' => (string) $session->id,
            'role' => 'assistant',
            'message' => $aiReply,
            'file_url' => $fileData['url'] ?? null,
            'file_path' => $fileData['path'] ?? null,
            'file_type' => $fileData['type'] ?? null,
            'file_name' => $fileData['name'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'reply' => $assistantMessage->message,
            'chat_session_id' => (string) $session->id,
            'redirect_url' => $redirectUrl,
            'file_url' => !empty($assistantMessage->file_path)
                ? route('chatbot.download', $assistantMessage->id)
                : null,
            'file_type' => $assistantMessage->file_type ?? null,
            'file_name' => $assistantMessage->file_name ?? null,
        ]);
    }

    public function ask(Request $request)
    {
        return $this->send($request);
    }

    public function download($messageId)
    {
        $user = Auth::user();

        $message = ChatMessage::where('id', $messageId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$message->file_path) {
            abort(404);
        }

        $path = storage_path('app/public/' . $message->file_path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download(
            $path,
            $message->file_name ?? basename($path)
        );
    }

    public function destroy($sessionId)
    {
        $user = Auth::user();

        $session = ChatSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        ChatMessage::where('chat_session_id', (string) $session->id)
            ->where('user_id', $user->id)
            ->delete();

        $session->delete();

        return redirect()->route('chatbot.index')
            ->with('success', 'Riwayat chat berhasil dihapus.');
    }

    private function getOrCreateSession(Request $request, string $userMessage, $uploadedFile = null): ChatSession
    {
        $user = Auth::user();
        $sessionId = $request->input('chat_session_id');

        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if ($session) {
                return $session;
            }
        }

        return ChatSession::create([
            'user_id' => $user->id,
            'title' => $this->makeSessionTitle($userMessage, $uploadedFile),
        ]);
    }

    private function makeSessionTitle(string $message, $uploadedFile = null): string
    {
        if ($message !== '') {
            $clean = strip_tags($message);
            $clean = preg_replace('/\s+/', ' ', $clean);
            $clean = trim($clean);

            return Str::limit($clean, 45, '');
        }

        if ($uploadedFile) {
            return 'Lampiran: ' . Str::limit($uploadedFile->getClientOriginalName(), 35, '');
        }

        return 'Obrolan Baru';
    }

    private function storeUserFile($file): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = Str::slug($baseName) ?: 'file';

        $storedPath = $file->storeAs(
            'chat_uploads',
            time() . '-' . $safeBaseName . '.' . $extension,
            'public'
        );

        $type = 'file';

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            $type = 'image';
        }

        if ($extension === 'pdf') {
            $type = 'pdf';
        }

        if (in_array($extension, ['doc', 'docx'])) {
            $type = 'word';
        }

        return [
            'type' => $type,
            'name' => $originalName,
            'url' => asset('storage/' . $storedPath),
            'path' => $storedPath,
            'extension' => $extension,
        ];
    }

    private function runAgent(ChatSession $session, string $userMessage, ?array $userFileData): array
    {
        $apiKey = env('GROQ_API_KEY');

dd([
    'api_key_exists' => !empty($apiKey),
    'api_key_prefix' => substr($apiKey, 0, 10),
]);

        if (!$apiKey) {
            return [
                'reply' => $this->fallbackReply($userMessage),
                'file_data' => null,
                'redirect_url' => null,
            ];
        }

        // 1. Definisikan System Prompt
        $systemPrompt = 'Kamu adalah asisten AI Activity Mahasiswa untuk mahasiswa bernama Lunox. Jawab dengan bahasa Indonesia yang jelas, natural, rapi, dan membantu. ' .
            'Kamu memiliki akses ke beberapa tools berikut jika pengguna memintanya: ' .
            '1. generate_document: gunakan jika user meminta untuk membuat/menghasilkan dokumen PDF atau Word. ' .
            '2. navigate_to_page: gunakan jika user meminta untuk membuka, melihat, pergi ke, atau pindah ke halaman tertentu (dashboard, drive, tugas, diskusi, aktivitas, atau chat baru). ' .
            'Jangan menuliskan link download manual atau markdown link download (seperti [Unduh](...)), karena tombol download file akan ditangani otomatis oleh sistem.';

        // 2. Buat messages payload termasuk riwayat chat untuk konteks percakapan
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ]
        ];

        // Ambil riwayat chat session
        $pastMessages = ChatMessage::where('chat_session_id', (string) $session->id)
            ->where('user_id', Auth::id())
            ->oldest()
            ->get();

        foreach ($pastMessages as $past) {
            if ($past->message) {
                $content = $past->message;
                if ($past->file_name) {
                    $content .= "\n\n[Lampiran file: " . $past->file_name . " (Tipe: " . $past->file_type . ")]";
                }
                $messages[] = [
                    'role' => $past->role === 'user' ? 'user' : 'assistant',
                    'content' => $content,
                ];
            }
        }

        // 3. Definisikan Tools
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'generate_document',
                    'description' => 'Membuat dokumen PDF atau Word berdasarkan judul dan isi konten yang diberikan.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'format' => [
                                'type' => 'string',
                                'enum' => ['pdf', 'word'],
                                'description' => 'Format file yang ingin dibuat (pdf atau word).'
                            ],
                            'title' => [
                                'type' => 'string',
                                'description' => 'Judul dokumen yang singkat, profesional, dan relevan dengan isi.'
                            ],
                            'content' => [
                                'type' => 'string',
                                'description' => 'Isi lengkap dokumen dalam bahasa Indonesia. Gunakan format paragraf atau poin-poin yang rapi. Jangan menuliskan link download atau URL apa pun.'
                            ],
                        ],
                        'required' => ['format', 'title', 'content'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'navigate_to_page',
                    'description' => 'Mengarahkan pengguna secara otomatis ke halaman atau menu tertentu dalam aplikasi CampusHub.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'destination' => [
                                'type' => 'string',
                                'enum' => ['dashboard', 'drive', 'tasks', 'discussions', 'activity', 'chatbot_new'],
                                'description' => 'Nama halaman tujuan navigasi.'
                            ],
                        ],
                        'required' => ['destination'],
                    ],
                ],
            ],
        ];

        try {
            // Panggilan pertama ke Groq
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 2200,
                    'tools' => $tools,
                    'tool_choice' => 'auto',
                ]);

            if (!$response->successful()) {
                return [
                    'reply' => $this->fallbackReply($userMessage),
                    'file_data' => null,
                    'redirect_url' => null,
                ];
            }

            $choice = $response->json('choices.0');
            $assistantMessage = $choice['message'] ?? null;

            if (!$assistantMessage) {
                return [
                    'reply' => $this->fallbackReply($userMessage),
                    'file_data' => null,
                    'redirect_url' => null,
                ];
            }

            $toolCalls = $assistantMessage['tool_calls'] ?? null;
            $replyContent = $assistantMessage['content'] ?? '';

            // Jika tidak memanggil tool, langsung kembalikan balasan teks
            if (empty($toolCalls)) {
                return [
                    'reply' => $this->cleanAiReply($replyContent),
                    'file_data' => null,
                    'redirect_url' => null,
                ];
            }

            // Eksekusi tool calls
            $fileData = null;
            $redirectUrl = null;

            // Simpan assistant message yang berisi tool_calls
            $messages[] = $assistantMessage;

            foreach ($toolCalls as $toolCall) {
                $toolId = $toolCall['id'];
                $funcName = $toolCall['function']['name'];
                $argumentsStr = $toolCall['function']['arguments'] ?? '{}';
                $args = json_decode($argumentsStr, true) ?: [];

                $toolResult = '';

                if ($funcName === 'generate_document') {
                    $format = $args['format'] ?? 'pdf';
                    $title = $args['title'] ?? 'Dokumen AI CampusHub';
                    $content = $args['content'] ?? '';

                    if ($format === 'pdf') {
                        $fileData = $this->makePdfFromAi($title, $content);
                        $toolResult = "File PDF berhasil dibuat dengan judul '{$title}' dan disimpan di: " . $fileData['url'];
                    } else {
                        $fileData = $this->makeWordFromAi($title, $content);
                        $toolResult = "File Word (.docx) berhasil dibuat dengan judul '{$title}' dan disimpan di: " . $fileData['url'];
                    }
                } elseif ($funcName === 'navigate_to_page') {
                    $destination = $args['destination'] ?? 'dashboard';

                    $routes = [
                        'dashboard' => 'dashboard',
                        'drive' => 'drive.index',
                        'tasks' => 'tasks.index',
                        'discussions' => 'discussions.index',
                        'activity' => 'activity.index',
                        'chatbot_new' => 'chatbot.index',
                    ];

                    $routeName = $routes[$destination] ?? 'dashboard';
                    $redirectUrl = route($routeName);
                    $toolResult = "Pengguna diarahkan ke halaman '{$destination}' di URL: {$redirectUrl}";
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolId,
                    'name' => $funcName,
                    'content' => $toolResult,
                ];
            }

            // Panggilan kedua ke Groq untuk mendapatkan tanggapan akhir
            $secondResponse = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                ]);
            if ($secondResponse->successful()) {
                $finalReply = $secondResponse->json('choices.0.message.content') ?? 'Tugas selesai dilakukan.';
                return [
                    'reply' => $this->cleanAiReply($finalReply),
                    'file_data' => $fileData,
                    'redirect_url' => $redirectUrl,
                ];
            }

            $fallbackReply = 'Tugas Anda telah diproses.';
            if ($fileData) {
                $fallbackReply .= ' Dokumen berhasil dibuat.';
            }
            if ($redirectUrl) {
                $fallbackReply .= ' Menuju halaman tujuan...';
            }

            return [
                'reply' => $fallbackReply,
                'file_data' => $fileData,
                'redirect_url' => $redirectUrl,
            ];

        } catch (\Throwable $e) {
            return [
                'reply' => 'Maaf, terjadi kesalahan saat memproses permintaan dengan asisten AI: ' . $e->getMessage(),
                'file_data' => null,
                'redirect_url' => null,
            ];
        }
    }

    private function fallbackReply(string $message): string
    {
        return "Saya sudah menerima permintaan kamu:\n\n{$message}\n\nNamun koneksi AI belum aktif atau API key belum tersedia. Silakan cek konfigurasi GROQ_API_KEY di file .env.";
    }

    private function cleanAiReply(string $text): string
    {
        $text = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/i', '$1', $text);
        $text = preg_replace('/https?:\/\/\S+/i', '', $text);
        $text = str_replace(['**', '__', '`'], '', $text);
        $text = preg_replace('/Dokumen ini dapat diunduh.*$/ims', '', $text);
        $text = preg_replace('/File ini dapat diunduh.*$/ims', '', $text);
        $text = preg_replace('/Unduh PDF.*$/im', '', $text);
        $text = preg_replace('/Unduh Word.*$/im', '', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    private function makePdfFromAi(string $title, string $content): array
    {
        $pdf = Pdf::loadView('generated.pdf-template', [
            'title' => $title,
            'content' => $content,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'generated/' . Str::slug($title) . '-' . time() . '.pdf';

        Storage::disk('public')->put($fileName, $pdf->output());

        return [
            'type' => 'pdf',
            'name' => basename($fileName),
            'path' => $fileName,
            'url' => asset('storage/' . $fileName),
        ];
    }

    private function makeWordFromAi(string $title, string $content): array
    {
        $phpWord = new PhpWord();

        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'marginTop' => 1200,
            'marginRight' => 1000,
            'marginBottom' => 1200,
            'marginLeft' => 1000,
        ]);

        $section->addText('Lunox AI Document', [
            'bold' => true,
            'size' => 10,
            'color' => '2563EB',
        ]);

        $section->addTextBreak(1);

        $section->addText($title, [
            'bold' => true,
            'size' => 20,
            'color' => '111827',
        ]);

        $section->addTextBreak(1);

        $section->addText('Dibuat otomatis oleh Lunox AI', [
            'italic' => true,
            'size' => 10,
            'color' => '6B7280',
        ]);

        $section->addTextBreak(2);

        $paragraphs = preg_split("/\r\n|\n|\r/", $content);

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                $section->addTextBreak(1);
                continue;
            }

            $section->addText($paragraph, [
                'size' => 12,
                'color' => '111827',
            ], [
                'spaceAfter' => 220,
                'lineHeight' => 1.35,
            ]);
        }

        $section->addTextBreak(2);

        $section->addText('Generated by Lunox AI', [
            'italic' => true,
            'size' => 9,
            'color' => '9CA3AF',
        ]);

        $fileName = 'generated/' . Str::slug($title) . '-' . time() . '.docx';
        $path = storage_path('app/public/' . $fileName);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return [
            'type' => 'word',
            'name' => basename($fileName),
            'path' => $fileName,
            'url' => asset('storage/' . $fileName),
        ];
    }
}
