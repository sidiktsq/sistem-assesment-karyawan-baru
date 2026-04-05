<div class="max-w-3xl mx-auto">
    <div class="glass-panel p-8 rounded-2xl shadow-2xl overflow-hidden relative border-white/10">
        <!-- Decoration -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>

        <div class="relative">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-xl premium-gradient flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white leading-tight">Selamat Datang, {{ $assignment->candidate->name }}</h1>
                    <p class="text-slate-400">Anda telah diundang untuk mengikuti asesmen di bawah ini.</p>
                </div>
            </div>

            <div class="bg-white/5 border border-white/5 rounded-xl p-6 mb-8">
                <h3 class="text-sm font-semibold text-blue-400 uppercase tracking-wider mb-4">Informasi Asesmen</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-slate-500 text-xs mb-1">Judul Asesmen</p>
                        <p class="text-white font-medium">{{ $assignment->assessment->title }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs mb-1">Tipe</p>
                        <p class="text-white font-medium capitalize">{{ $assignment->assessment->type }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs mb-1">Durasi</p>
                        <p class="text-white font-medium">{{ $assignment->assessment->duration_minutes }} Menit</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs mb-1">Total Soal</p>
                        <p class="text-white font-medium">{{ $assignment->assessment->questions->count() }} Soal</p>
                    </div>
                </div>
            </div>

            <div class="prose prose-invert max-w-none mb-8 text-slate-300">
                <h3 class="text-white">Instruksi Pengerjaan:</h3>
                <ul class="space-y-2 list-disc pl-5">
                    <li>Gunakan koneksi internet yang stabil selama pengerjaan.</li>
                    <li>Waktu akan mulai dihitung segera setelah Anda menekan tombol "Mulai Ujian".</li>
                    <li>Jawaban Anda akan disimpan otomatis setiap kali Anda beralih soal atau menekan pilihan.</li>
                    <li>Jangan menutup tab atau browser sebelum Anda menekan tombol "Selesai" di akhir ujian.</li>
                </ul>
            </div>

            <button wire:click="startExam" class="w-full py-4 px-6 rounded-xl premium-gradient text-white font-bold text-lg shadow-xl shadow-blue-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <span>Mulai Ujian Sekarang</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </div>
</div>
