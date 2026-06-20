<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\DriveFile;
use App\Models\Task;
use App\Services\SupabaseStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

        $localPath = storage_path('app/public/' . $message->file_path);

        if (file_exists($localPath)) {
            return response()->download(
                $localPath,
                $message->file_name ?? basename($localPath)
            );
        }

        if (!empty($message->file_url)) {
            return redirect($message->file_url);
        }

        abort(404);
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

    // =========================================================
    // PRIVATE METHODS
    // =========================================================

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

    private function getOrCreateFolder(?string $folderName = null): string
    {
        $user = Auth::user();
        $folderName = trim($folderName ?? 'Hasil AI');
        if ($folderName === '' || strtolower($folderName) === 'root') {
            return 'root';
        }

        $existing = DriveFile::where('user_id', (string) $user->id)
            ->where('is_folder', true)
            ->where('name', $folderName)
            ->first();

        if ($existing) {
            return $existing->name;
        }

        DriveFile::create([
            'user_id'       => (string) $user->id,
            'folder'        => 'root',
            'name'          => $folderName,
            'original_name' => $folderName,
            'mime_type'     => 'folder',
            'size'          => 0,
            'path'          => $folderName,
            'url'           => null,
            'is_folder'     => true,
        ]);

        return $folderName;
    }

    private function getOrCreateAiFolder(): string
    {
        return $this->getOrCreateFolder('Hasil AI');
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
            '3. save_file_to_drive: gunakan jika user meminta untuk menyimpan/mengunggah file atau lampiran chat ke Drive. ' .
            '4. create_task: gunakan jika user meminta untuk membuat/mencatat tugas kuliah baru. ' .
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
            [
                'type' => 'function',
                'function' => [
                    'name' => 'save_file_to_drive',
                    'description' => 'Menyimpan file yang diunggah oleh pengguna di chat (baik file di chat saat ini atau dari riwayat chat sesi ini) ke dalam Drive.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'folder_name' => [
                                'type' => 'string',
                                'description' => 'Nama folder tujuan di Drive (contoh: "Tugas", "Materi", "Hasil AI"). Jika dikosongkan, file akan diletakkan di root atau folder default.'
                            ],
                            'custom_name' => [
                                'type' => 'string',
                                'description' => 'Nama file kustom baru jika pengguna ingin mengubah nama filenya saat disimpan. Sertakan ekstensi file asli yang sesuai (misal: dokumen.pdf).'
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_task',
                    'description' => 'Membuat atau mencatat tugas kuliah baru ke dalam database.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => [
                                'type' => 'string',
                                'description' => 'Judul tugas kuliah yang ingin dibuat (contoh: "Tugas Kalkulus I", "Resume Jurnal Pemrograman Web").'
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Deskripsi, petunjuk, atau catatan tambahan untuk tugas tersebut.'
                            ],
                            'deadline' => [
                                'type' => 'string',
                                'description' => 'Tanggal deadline/tenggat waktu pengumpulan tugas dengan format YYYY-MM-DD (contoh: "2026-06-25"). Tanyakan atau tentukan tahun saat ini (2026) jika tidak disebutkan.'
                            ],
                            'status' => [
                                'type' => 'string',
                                'enum' => ['belum', 'proses', 'selesai'],
                                'description' => 'Status pengerjaan tugas saat ini ("belum", "proses", atau "selesai"). Default: "belum".'
                            ],
                            'attach_file' => [
                                'type' => 'boolean',
                                'description' => 'Set ke true jika pengguna secara eksplisit meminta untuk menyertakan/melampirkan file/dokumen yang dikirim di chat ke tugas ini.'
                            ],
                        ],
                        'required' => ['title'],
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
                        $fileData = $this->makePdfFromAi($title, $content, app(SupabaseStorageService::class));
                        $toolResult = "File PDF berhasil dibuat dengan judul '{$title}' dan disimpan di folder Hasil AI.";
                    } else {
                        $fileData = $this->makeWordFromAi($title, $content, app(SupabaseStorageService::class));
                        $toolResult = "File Word (.docx) berhasil dibuat dengan judul '{$title}' dan disimpan di folder Hasil AI.";
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
                } elseif ($funcName === 'save_file_to_drive') {
                    $folderName = $args['folder_name'] ?? 'Hasil AI';
                    $customName = $args['custom_name'] ?? null;

                    // 1. Coba cari file di request saat ini terlebih dahulu
                    $sourceFile = null;
                    if ($userFileData) {
                        $sourceFile = $userFileData;
                    } else {
                        // 2. Cari di riwayat pesan sesi chat ini dari yang terbaru
                        $lastFileMessage = ChatMessage::where('chat_session_id', (string) $session->id)
                            ->where('user_id', Auth::id())
                            ->whereNotNull('file_path')
                            ->latest()
                            ->first();

                        if ($lastFileMessage) {
                            $sourceFile = [
                                'path' => $lastFileMessage->file_path,
                                'name' => $lastFileMessage->file_name,
                                'type' => $lastFileMessage->file_type,
                            ];
                        }
                    }

                    if ($sourceFile) {
                        $localPath = storage_path('app/public/' . $sourceFile['path']);
                        if (file_exists($localPath)) {
                            // Tentukan nama file tujuan
                            $finalName = $customName ?: $sourceFile['name'];

                            // Jika ada nama kustom, pastikan ekstensi sesuai dengan aslinya
                            if ($customName) {
                                $origExt = pathinfo($sourceFile['name'], PATHINFO_EXTENSION);
                                $newExt = pathinfo($customName, PATHINFO_EXTENSION);
                                if (strtolower($origExt) !== strtolower($newExt) && $origExt !== '') {
                                    $finalName = pathinfo($customName, PATHINFO_FILENAME) . '.' . $origExt;
                                }
                            }

                            // Buat instance UploadedFile agar sesuai dengan service
                            $mimeType = mime_content_type($localPath) ?: 'application/octet-stream';
                            $uploadedFile = new UploadedFile(
                                $localPath,
                                $finalName,
                                $mimeType,
                                null,
                                true // Enable test mode to bypass normal uploaded file validation
                            );

                            $targetFolder = $this->getOrCreateFolder($folderName);
                            $storage = app(SupabaseStorageService::class);
                            $upload = $storage->uploadDriveFile($uploadedFile, $targetFolder);

                            DriveFile::create([
                                'user_id'       => (string) Auth::id(),
                                'folder'        => $targetFolder,
                                'name'          => $upload['original_name'],
                                'original_name' => $upload['original_name'],
                                'mime_type'     => $upload['mime_type'],
                                'size'          => $upload['size'],
                                'path'          => $upload['path'],
                                'url'           => $upload['url'],
                                'is_folder'     => false,
                            ]);

                            $toolResult = "File '{$finalName}' berhasil disimpan ke Drive di folder '{$targetFolder}'.";
                        } else {
                            $toolResult = "Gagal menyimpan file ke Drive: file fisik tidak ditemukan di server lokal.";
                        }
                    } else {
                        $toolResult = "Gagal menyimpan file ke Drive: tidak ada file/lampiran yang ditemukan di chat saat ini atau di riwayat chat sesi ini.";
                    }
                } elseif ($funcName === 'create_task') {
                    $title = $args['title'] ?? 'Tugas Baru';
                    $description = $args['description'] ?? null;
                    $deadline = $args['deadline'] ?? null;
                    $status = $args['status'] ?? 'belum';
                    $attachFile = $args['attach_file'] ?? false;

                    $fileUrl = null;
                    $filePath = null;
                    $fileName = null;

                    if ($attachFile) {
                        $sourceFile = null;
                        if ($userFileData) {
                            $sourceFile = $userFileData;
                        } else {
                            $lastFileMessage = ChatMessage::where('chat_session_id', (string) $session->id)
                                ->where('user_id', Auth::id())
                                ->whereNotNull('file_path')
                                ->latest()
                                ->first();

                            if ($lastFileMessage) {
                                $sourceFile = [
                                    'path' => $lastFileMessage->file_path,
                                    'name' => $lastFileMessage->file_name,
                                    'type' => $lastFileMessage->file_type,
                                ];
                            }
                        }

                        if ($sourceFile) {
                            $localPath = storage_path('app/public/' . $sourceFile['path']);
                            if (file_exists($localPath)) {
                                $mimeType = mime_content_type($localPath) ?: 'application/octet-stream';
                                $uploadedFile = new UploadedFile(
                                    $localPath,
                                    $sourceFile['name'],
                                    $mimeType,
                                    null,
                                    true
                                );

                                $storage = app(SupabaseStorageService::class);
                                $upload = $storage->uploadTaskFile($uploadedFile);
                                $fileUrl = $upload['url'];
                                $filePath = $upload['path'];
                                $fileName = $upload['original_name'];
                            }
                        }
                    }

                    // Simpan Task ke Database
                    Task::create([
                        'user_id' => (string) Auth::id(),
                        'title' => $title,
                        'description' => $description,
                        'deadline' => $deadline,
                        'status' => $status,
                        'file_url' => $fileUrl,
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                    ]);

                    \App\Services\ActivityLogger::log('task_create', 'Membuat tugas via AI: ' . $title);

                    $toolResult = "Tugas '{$title}' dengan status '{$status}' berhasil dibuat.";
                    if ($deadline) {
                        $toolResult .= " Deadline pengumpulan: {$deadline}.";
                    }
                    if ($fileUrl) {
                        $toolResult .= " File lampiran '{$fileName}' berhasil dikaitkan.";
                    }
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
                $fallbackReply .= ' Dokumen berhasil dibuat dan disimpan di folder Hasil AI.';
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

    private function makePdfFromAi(string $title, string $content, SupabaseStorageService $storage): array
    {
        $pdf = Pdf::loadView('generated.pdf-template', [
            'title'   => $title,
            'content' => $content,
        ]);
        $pdf->setPaper('a4', 'portrait');

        $fileNameOnly = Str::slug($title) . '-' . time() . '.pdf';
        $tempPath = sys_get_temp_dir() . '/' . $fileNameOnly;
        file_put_contents($tempPath, $pdf->output());

        $uploadedFile = new UploadedFile($tempPath, $fileNameOnly, 'application/pdf', null, true);

        $aiFolder = $this->getOrCreateAiFolder();

        $upload = $storage->uploadDriveFile($uploadedFile, $aiFolder);

        DriveFile::create([
            'user_id'       => (string) Auth::id(),
            'folder'        => $aiFolder,
            'name'          => $upload['original_name'],
            'original_name' => $upload['original_name'],
            'mime_type'     => $upload['mime_type'],
            'size'          => $upload['size'],
            'path'          => $upload['path'],
            'url'           => $upload['url'],
            'is_folder'     => false,
        ]);

        @unlink($tempPath);

        return [
            'type' => 'pdf',
            'name' => $upload['original_name'],
            'path' => $upload['path'],
            'url'  => $upload['url'],
        ];
    }

    private function makeWordFromAi(string $title, string $content, SupabaseStorageService $storage): array
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

        $fileNameOnly = Str::slug($title) . '-' . time() . '.docx';
        $tempPath = sys_get_temp_dir() . '/' . $fileNameOnly;

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            $fileNameOnly,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $aiFolder = $this->getOrCreateAiFolder();

        $upload = $storage->uploadDriveFile($uploadedFile, $aiFolder);

        DriveFile::create([
            'user_id'       => (string) Auth::id(),
            'folder'        => $aiFolder,
            'name'          => $upload['original_name'],
            'original_name' => $upload['original_name'],
            'mime_type'     => $upload['mime_type'],
            'size'          => $upload['size'],
            'path'          => $upload['path'],
            'url'           => $upload['url'],
            'is_folder'     => false,
        ]);

        @unlink($tempPath);

        return [
            'type' => 'word',
            'name' => $upload['original_name'],
            'path' => $upload['path'],
            'url'  => $upload['url'],
        ];
    }
}