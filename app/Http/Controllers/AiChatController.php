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

        $currentSession = ChatSession::where('_id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $messages = ChatMessage::where('chat_session_id', (string) $currentSession->_id)
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

        $navigation = $this->detectNavigationIntent($userMessage);

        if ($navigation && !$uploadedFile) {
            ChatMessage::create([
                'user_id' => $user->id,
                'chat_session_id' => (string) $session->_id,
                'role' => 'user',
                'message' => $userMessage,
            ]);

            $reply = 'Baik, saya arahkan ke halaman ' . $navigation['label'] . '.';

            ChatMessage::create([
                'user_id' => $user->id,
                'chat_session_id' => (string) $session->_id,
                'role' => 'assistant',
                'message' => $reply,
            ]);

            return response()->json([
                'success' => true,
                'reply' => $reply,
                'chat_session_id' => (string) $session->_id,
                'redirect_url' => $navigation['url'],
                'file_url' => null,
                'file_type' => null,
                'file_name' => null,
            ]);
        }

        $userFileData = null;

        if ($uploadedFile) {
            $userFileData = $this->storeUserFile($uploadedFile);
        }

        ChatMessage::create([
            'user_id' => $user->id,
            'chat_session_id' => (string) $session->_id,
            'role' => 'user',
            'message' => $userMessage !== '' ? $userMessage : 'Saya mengupload lampiran.',
            'file_url' => $userFileData['url'] ?? null,
            'file_path' => $userFileData['path'] ?? null,
            'file_type' => $userFileData['type'] ?? null,
            'file_name' => $userFileData['name'] ?? null,
        ]);

        $intent = $this->detectGenerateIntent($userMessage);
        $aiPrompt = $this->buildPrompt($userMessage, $uploadedFile, $userFileData, $intent);
        $aiReply = $this->askAi($aiPrompt, $intent);
        $aiReply = $this->cleanAiReply($aiReply);

        $fileData = null;

        if ($intent === 'pdf') {
            $fileData = $this->makePdfFromAi($userMessage, $aiReply);
            $aiReply .= "\n\nPDF berhasil dibuat. Tekan tombol download di bawah untuk mengunduh file.";
        }

        if ($intent === 'word') {
            $fileData = $this->makeWordFromAi($userMessage, $aiReply);
            $aiReply .= "\n\nFile Word berhasil dibuat. Tekan tombol download di bawah untuk mengunduh file.";
        }

        $assistantMessage = ChatMessage::create([
            'user_id' => $user->id,
            'chat_session_id' => (string) $session->_id,
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
            'chat_session_id' => (string) $session->_id,
            'redirect_url' => route('chatbot.show', $session->_id),
            'file_url' => !empty($assistantMessage->file_path)
                ? route('chatbot.download', $assistantMessage->_id)
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

        $message = ChatMessage::where('_id', $messageId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (empty($message->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        if (!Storage::disk('public')->exists($message->file_path)) {
            abort(404, 'File tidak tersedia di storage.');
        }

        $absolutePath = storage_path('app/public/' . $message->file_path);
        $downloadName = $message->file_name ?: basename($absolutePath);

        return response()->download($absolutePath, $downloadName);
    }

    public function destroy($sessionId)
    {
        $user = Auth::user();

        $session = ChatSession::where('_id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        ChatMessage::where('chat_session_id', (string) $session->_id)
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
            $session = ChatSession::where('_id', $sessionId)
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

    private function detectNavigationIntent(string $message): ?array
    {
        $text = strtolower($message);
        $text = preg_replace('/\s+/', ' ', $text);

        $routes = [
            [
                'keywords' => [
                    'ke dashboard',
                    'buka dashboard',
                    'pindah dashboard',
                    'pindah ke dashboard',
                    'halaman dashboard',
                    'menu dashboard',
                    'dashboard',
                ],
                'route' => 'dashboard',
                'label' => 'Dashboard',
            ],
            [
                'keywords' => [
                    'ke drive',
                    'buka drive',
                    'pindah drive',
                    'pindah ke drive',
                    'halaman drive',
                    'menu drive',
                    'file saya',
                    'penyimpanan',
                    'storage',
                    'drive',
                ],
                'route' => 'drive.index',
                'label' => 'Drive',
            ],
            [
                'keywords' => [
                    'ke tugas',
                    'buka tugas',
                    'pindah tugas',
                    'pindah ke tugas',
                    'halaman tugas',
                    'menu tugas',
                    'task',
                    'tasks',
                    'tugas',
                ],
                'route' => 'tasks.index',
                'label' => 'Tugas',
            ],
            [
                'keywords' => [
                    'ke diskusi',
                    'buka diskusi',
                    'pindah diskusi',
                    'pindah ke diskusi',
                    'halaman diskusi',
                    'menu diskusi',
                    'discussion',
                    'discussions',
                    'diskusi',
                ],
                'route' => 'discussions.index',
                'label' => 'Diskusi',
            ],
            [
                'keywords' => [
                    'ke aktivitas',
                    'buka aktivitas',
                    'pindah aktivitas',
                    'pindah ke aktivitas',
                    'riwayat aktivitas',
                    'halaman aktivitas',
                    'menu aktivitas',
                    'activity',
                    'aktivitas',
                ],
                'route' => 'activity.index',
                'label' => 'Aktivitas',
            ],
            [
                'keywords' => [
                    'chat baru',
                    'obrolan baru',
                    'mulai chat baru',
                    'buat chat baru',
                ],
                'route' => 'chatbot.index',
                'label' => 'Obrolan Baru',
            ],
        ];

        foreach ($routes as $item) {
            foreach ($item['keywords'] as $keyword) {
                if ($text === $keyword || str_contains($text, $keyword)) {
                    return [
                        'route' => $item['route'],
                        'label' => $item['label'],
                        'url' => route($item['route']),
                    ];
                }
            }
        }

        return null;
    }

    private function buildPrompt(string $message, $uploadedFile = null, ?array $fileData = null, ?string $intent = null): string
    {
        $prompt = trim($message);

        if ($intent === 'pdf') {
            $prompt .= "\n\nBuat isi dokumen yang rapi dan profesional untuk dijadikan PDF. Gunakan struktur dokumen yang menurutmu paling tepat. Gunakan format sederhana jika diperlukan: # untuk judul utama, ## untuk subjudul, ### untuk sub-subjudul, - untuk poin, dan angka untuk langkah. Jangan membuat link download atau URL apa pun.";
        }

        if ($intent === 'word') {
            $prompt .= "\n\nBuat isi dokumen yang rapi dan profesional untuk dijadikan file Word. Gunakan struktur dokumen yang menurutmu paling tepat. Gunakan format sederhana jika diperlukan: # untuk judul utama, ## untuk subjudul, ### untuk sub-subjudul, - untuk poin, dan angka untuk langkah. Jangan membuat link download atau URL apa pun.";
        }

        if ($uploadedFile && $fileData) {
            $prompt .= "\n\nUser juga mengupload file:";
            $prompt .= "\nNama file: " . $fileData['name'];
            $prompt .= "\nTipe file: " . $fileData['type'];

            if ($fileData['type'] === 'image') {
                $prompt .= "\nGambar sudah diterima. Jika belum bisa membaca isi gambar secara visual, minta user menjelaskan bagian yang ingin dianalisis.";
            }

            if ($fileData['type'] === 'pdf' || $fileData['type'] === 'word') {
                $prompt .= "\nFile sudah diterima. Jika isi file belum bisa dibaca otomatis, minta user menyalin bagian pentingnya.";
            }
        }

        if ($prompt === '') {
            $prompt = 'Saya mengupload file. Tolong bantu jelaskan langkah berikutnya.';
        }

        return $prompt;
    }

    private function askAi(string $message, ?string $intent = null): string
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            return $this->fallbackReply($message);
        }

        $systemPrompt = 'Kamu adalah asisten AI CampusHub untuk mahasiswa. Jawab dengan bahasa Indonesia yang jelas, natural, rapi, dan membantu. Jika user meminta PDF atau Word, pikirkan sendiri struktur dokumen yang paling cocok. Buat dokumen seperti tulisan manusia yang siap dibaca, dengan judul, subjudul, paragraf, dan poin jika memang diperlukan. Untuk struktur dokumen, boleh gunakan #, ##, ###, daftar angka, dan bullet. Jangan membuat link download, jangan menulis URL download, dan jangan menulis markdown link seperti [Unduh PDF](...). Backend akan membuat tombol download file secara otomatis.';

        if ($intent === 'pdf' || $intent === 'word') {
            $systemPrompt .= ' Untuk dokumen, fokus pada isi yang lengkap dan terstruktur. Jangan jelaskan bahwa kamu sedang membuat file. Jangan sertakan instruksi teknis.';
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2200,
                ]);

            if (!$response->successful()) {
                return $this->fallbackReply($message);
            }

            return $response->json('choices.0.message.content')
                ?? $this->fallbackReply($message);

        } catch (\Throwable $e) {
            return $this->fallbackReply($message);
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

    private function detectGenerateIntent(string $message): ?string
    {
        $text = strtolower($message);

        if (
            str_contains($text, 'buat pdf') ||
            str_contains($text, 'buatkan pdf') ||
            str_contains($text, 'bikin pdf') ||
            str_contains($text, 'bikinin pdf') ||
            str_contains($text, 'jadikan pdf') ||
            str_contains($text, 'file pdf') ||
            str_contains($text, 'download pdf') ||
            str_contains($text, 'export pdf') ||
            str_contains($text, 'unduh pdf')
        ) {
            return 'pdf';
        }

        if (
            str_contains($text, 'buat word') ||
            str_contains($text, 'buatkan word') ||
            str_contains($text, 'bikin word') ||
            str_contains($text, 'bikinin word') ||
            str_contains($text, 'buat docx') ||
            str_contains($text, 'file word') ||
            str_contains($text, 'jadikan word') ||
            str_contains($text, 'dokumen word') ||
            str_contains($text, 'export word') ||
            str_contains($text, 'unduh word')
        ) {
            return 'word';
        }

        return null;
    }

    private function makeTitle(string $message): string
    {
        $clean = Str::of($message)
            ->lower()
            ->replace([
                'buatkan',
                'buat',
                'bikin',
                'bikinin',
                'pdf',
                'word',
                'docx',
                'file',
                'dokumen',
                'tentang',
                'jadikan',
                'download',
                'export',
                'unduh',
            ], '')
            ->trim();

        $title = Str::title((string) $clean);

        if (strlen($title) < 5) {
            $title = 'Dokumen AI CampusHub';
        }

        return Str::limit($title, 80, '');
    }

    private function makePdfFromAi(string $userMessage, string $content): array
    {
        $title = $this->makeTitle($userMessage);

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

    private function makeWordFromAi(string $userMessage, string $content): array
    {
        $title = $this->makeTitle($userMessage);

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
