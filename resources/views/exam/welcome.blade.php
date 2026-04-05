@extends('layouts.candidate')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-slate-900/50 backdrop-blur-xl rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
        <!-- Main Card Header/Icon -->
        <div class="p-8 pb-0 text-center">
            <div class="w-24 h-24 bg-blue-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-blue-500/20">
                <i class="fas fa-graduation-cap text-blue-400 text-4xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Selamat Datang</h1>
            <p class="text-blue-400 font-medium tracking-wide uppercase text-sm mb-8">{{ $assignment->assessment->title }}</p>
        </div>

        <!-- Info Grid -->
        <div class="px-8 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/5 rounded-2xl p-6 border border-white/5">
                <div class="space-y-4">
                    <h3 class="flex items-center text-sm font-bold text-white uppercase tracking-wider">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Informasi Assessment
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Durasi Pengerjaan</span>
                            <span class="px-3 py-1 bg-slate-800 rounded-lg text-white font-semibold">{{ $assignment->assessment->duration_minutes }} Menit</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Jumlah Pertanyaan</span>
                            <span class="px-3 py-1 bg-slate-800 rounded-lg text-white font-semibold">{{ $assignment->assessment->questions->count() }} Soal</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Nilai Kelulusan</span>
                            <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg font-bold border border-blue-500/20">{{ $assignment->assessment->passing_score }}%</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="flex items-center text-sm font-bold text-white uppercase tracking-wider">
                        <i class="fas fa-list-ul mr-2 text-blue-500"></i>
                        Petunjuk Pengerjaan
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start text-sm text-slate-400">
                            <i class="fas fa-check-circle mt-0.5 mr-3 text-blue-500/50"></i>
                            <span>Pastikan koneksi internet Anda stabil selama pengerjaan.</span>
                        </li>
                        <li class="flex items-start text-sm text-slate-400">
                            <i class="fas fa-check-circle mt-0.5 mr-3 text-blue-500/50"></i>
                            <span>Kerjakan dengan jujur dan teliti sesuai kemampuan.</span>
                        </li>
                        <li class="flex items-start text-sm text-slate-400">
                            <i class="fas fa-check-circle mt-0.5 mr-3 text-blue-500/50"></i>
                            <span>Waktu akan mulai berjalan saat Anda menekan tombol mulai.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="mt-8 bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-exclamation-triangle text-amber-500"></i>
                </div>
                <p class="text-sm text-amber-200/80 leading-relaxed">
                    Pastikan Anda dalam kondisi siap. Assessment ini memiliki batas waktu dan tidak dapat diulang kembali setelah disubmit.
                </p>
            </div>

            <!-- Action Button -->
            <div class="mt-10">
                <a href="{{ route('exam.take', $token) }}" class="group relative w-full flex items-center justify-center px-8 py-5 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-bold text-lg transition-all duration-300 shadow-xl shadow-blue-600/20 hover:shadow-blue-600/40 transform hover:-translate-y-1">
                    <span class="flex items-center">
                        <i class="fas fa-play mr-3 group-hover:scale-125 transition-transform"></i>
                        Mulai Assessment Sekarang
                    </span>
                    <i class="fas fa-arrow-right absolute right-8 opacity-0 group-hover:opacity-100 group-hover:translate-x-2 transition-all"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Additional Footer Info -->
    <div class="mt-8 text-center space-y-2">
        <p class="text-slate-500 text-xs">
            <i class="fas fa-shield-alt mr-1"></i>
            Assessment ini diproteksi oleh Sistem Keamanan Terpadu
        </p>
    </div>
</div>
@endsection
