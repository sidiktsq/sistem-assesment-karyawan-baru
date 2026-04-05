<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fas fa-check text-white text-sm"></i>
                </div>
                <span class="text-lg font-bold tracking-tight text-gray-900 uppercase">Sistem Asesmen</span>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <div class="text-center mb-8">
                            @if($assignment->status === 'completed' || $assignment->status === 'reviewed')
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                                </div>
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">Assessment Selesai!</h1>
                                <p class="text-gray-600">Terima kasih telah menyelesaikan assessment</p>
                            @else
                                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-hourglass-half text-yellow-600 text-3xl"></i>
                                </div>
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">Assessment Belum Selesai</h1>
                                <p class="text-gray-600">Silakan menyelesaikan assessment terlebih dahulu</p>
                            @endif
                        </div>

                        @if($assignment->status === 'completed' || $assignment->status === 'reviewed')
                            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                                <h3 class="font-semibold text-gray-900 mb-4">Hasil Assessment</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-blue-600">{{ $assignment->percentage ?? 0 }}%</div>
                                        <div class="text-sm text-gray-600">Skor Akhir</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-green-600">{{ $assignment->assessment->questions->count() }}</div>
                                        <div class="text-sm text-gray-600">Total Soal</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-purple-600">{{ $assignment->assessment->duration_minutes }} menit</div>
                                        <div class="text-sm text-gray-600">Durasi</div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <p class="text-gray-600 mb-4">Hasil assessment Anda telah tersimpan. Tim HRD akan menghubungi Anda untuk langkah selanjutnya.</p>
                                <button onclick="window.print()" class="bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-print mr-2"></i> Cetak Hasil
                                </button>
                            </div>
                        @else
                            <div class="text-center">
                                <a href="{{ route('exam.take', $token) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition-colors inline-flex items-center">
                                    <i class="fas fa-play mr-2"></i>
                                    Lanjutkan Assessment
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Assessment System. All rights reserved.
            </div>
        </footer>
    </div>
</body>
</html>
