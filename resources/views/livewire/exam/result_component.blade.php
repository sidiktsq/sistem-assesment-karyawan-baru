<div class="max-w-2xl mx-auto text-center">
    <div class="glass-panel p-12 rounded-3xl border-white/10 shadow-2xl relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative">
            <div class="w-20 h-20 bg-emerald-500/20 rounded-2xl flex items-center justify-center mx-auto mb-8 border border-emerald-500/30">
                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-white mb-4">Assessment Selesai!</h1>
            <p class="text-slate-400 text-lg mb-8">Terima kasih, {{ $assignment->candidate->name }}. Jawaban Anda telah berhasil kami simpan dan sedang dalam proses peninjauan.</p>

            <div class="bg-white/5 border border-white/5 rounded-2xl p-6 mb-8 text-left inline-block w-full">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-xl">📝</div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Asesmen</p>
                        <p class="text-white font-medium">{{ $assignment->assessment->title }}</p>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-white/5 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-slate-500 text-xs">Selesai Pada</p>
                        <p class="text-white text-sm font-medium">{{ $assignment->completed_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Status</p>
                        <p class="text-emerald-400 text-sm font-bold uppercase tracking-tighter">Tersimpan</p>
                    </div>
                </div>
            </div>

            <p class="text-slate-500 text-sm italic">
                Kami akan menghubungi Anda melalui email terkait hasil asesmen ini. <br>
                Anda sekarang dapat menutup halaman ini.
            </p>
        </div>
    </div>
</div>
