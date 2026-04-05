<div class="grid grid-cols-1 lg:grid-cols-4 gap-8" x-data="{ 
    timeLeft: @entangle('timeLeft'),
    timerInterval: null,
    formatTime(seconds) {
        if (seconds <= 0) return '00:00:00';
        const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
        const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
        const s = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${h}:${m}:${s}`;
    }
}" x-init="
    timerInterval = setInterval(() => {
        if (timeLeft > 0) timeLeft--;
        else {
            clearInterval(timerInterval);
            $wire.finishExam();
        }
    }, 1000);
">
    <!-- Left: Navigation & Timer -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Timer Card -->
        <div class="glass-panel p-6 rounded-2xl border-white/10 shadow-xl">
            <h3 class="text-slate-500 text-xs font-semibold uppercase tracking-wider mb-2">Sisa Waktu</h3>
            <div class="text-4xl font-bold font-mono tracking-tighter text-white" :class="timeLeft < 300 ? 'text-red-500 animate-pulse' : ''" x-text="formatTime(timeLeft)">
                {{ sprintf('%02d:%02d:%02d', floor($timeLeft/3600), floor(($timeLeft%3600)/60), $timeLeft%60) }}
            </div>
            <div class="mt-4 h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full premium-gradient transition-all duration-1000" :style="`width: ${(timeLeft / ({{ $assignment->assessment->duration_minutes * 60 }})) * 100}%` "></div>
            </div>
        </div>

        <!-- Question Grid -->
        <div class="glass-panel p-6 rounded-2xl border-white/10 shadow-xl">
            <h3 class="text-slate-500 text-xs font-semibold uppercase tracking-wider mb-4">Navigasi Soal</h3>
            <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                @foreach($questions as $index => $q)
                    <button 
                        wire:click="goToQuestion({{ $index }})"
                        class="aspect-square rounded-lg flex items-center justify-center text-sm font-bold transition-all border
                        {{ $currentIndex === $index ? 'bg-blue-600 border-blue-400 text-white shadow-lg shadow-blue-500/20' : 
                           (isset($answers[$q->id]) && $answers[$q->id] !== '' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400' : 'bg-slate-800/50 border-white/5 text-slate-400 hover:bg-slate-700/50') }}"
                    >
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
            
            <button wire:click="finishExam" wire:confirm="Apakah Anda yakin ingin menyelesaikan ujian ini?" class="w-full mt-8 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-all shadow-lg shadow-emerald-600/20">
                Selesaikan Ujian
            </button>
        </div>
    </div>

    <!-- Right: Question Interface -->
    <div class="lg:col-span-3">
        @if($currentQuestion)
            <div class="glass-panel rounded-2xl border-white/10 shadow-xl overflow-hidden min-h-[500px] flex flex-col">
                <!-- Question Header -->
                <div class="p-6 border-b border-white/5 bg-white/5 flex justify-between items-center">
                    <span class="text-blue-400 font-bold uppercase tracking-widest text-xs">Pertanyaan {{ $currentIndex + 1 }} dari {{ $questions->count() }}</span>
                    <span class="px-3 py-1 bg-slate-800 border border-white/10 rounded-full text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $currentQuestion->type }}</span>
                </div>

                <!-- Question Body -->
                <div class="p-8 flex-grow">
                    <div class="prose prose-invert max-w-none text-xl text-white font-medium mb-12 leading-relaxed">
                        {!! nl2br(e($currentQuestion->question_text)) !!}
                    </div>

                    <!-- Answers -->
                    <div class="space-y-4">
                        @if($currentQuestion->type === 'multiple_choice' || $currentQuestion->type === 'personality')
                            <div class="grid grid-cols-1 gap-3">
                                @php $options = is_array($currentQuestion->options) ? $currentQuestion->options : json_decode($currentQuestion->options, true) @endphp
                                @foreach($options as $opt)
                                    <label class="group relative flex items-center p-4 rounded-xl border-2 transition-all cursor-pointer
                                        {{ (isset($answers[$currentQuestion->id]) && $answers[$currentQuestion->id] == $opt['option']) 
                                            ? 'bg-blue-600/10 border-blue-500 text-white ring-2 ring-blue-500/20' 
                                            : 'bg-slate-900/50 border-white/5 text-slate-400 hover:bg-slate-800 hover:border-white/10' }}">
                                        <input type="radio" name="answer-{{ $currentQuestion->id }}" value="{{ $opt['option'] }}" 
                                            wire:click="saveAnswer({{ $currentQuestion->id }}, '{{ $opt['option'] }}')"
                                            class="sr-only"
                                            {{ (isset($answers[$currentQuestion->id]) && $answers[$currentQuestion->id] == $opt['option']) ? 'checked' : '' }}>
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center mr-4 transition-all
                                            {{ (isset($answers[$currentQuestion->id]) && $answers[$currentQuestion->id] == $opt['option']) 
                                                ? 'border-blue-500 bg-blue-500' 
                                                : 'border-white/10 group-hover:border-white/20' }}">
                                            @if(isset($answers[$currentQuestion->id]) && $answers[$currentQuestion->id] == $opt['option'])
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                            @endif
                                        </div>
                                        <div class="flex-grow">
                                            <span class="text-xs font-bold text-slate-500 mr-2 uppercase tracking-tight">{{ $opt['option'] }}.</span>
                                            <span class="text-lg">{{ $opt['text'] }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @elseif($currentQuestion->type === 'essay')
                            <textarea 
                                wire:model.blur="answers.{{ $currentQuestion->id }}"
                                wire:change="saveAnswer({{ $currentQuestion->id }}, $event.target.value)"
                                class="w-full bg-slate-900/50 border-white/5 rounded-2xl p-6 text-white placeholder-slate-600 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 outline-none transition-all"
                                rows="10"
                                placeholder="Ketikkan jawaban Anda di sini..."></textarea>
                            <p class="mt-4 text-xs text-slate-500">Jawaban akan disimpan otomatis saat Anda berhenti mengetik atau berpindah soal.</p>
                        @endif
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div class="px-6 py-6 border-t border-white/5 bg-white/5 flex justify-between items-center mt-auto">
                    <button wire:click="goToQuestion({{ $currentIndex - 1 }})" {{ $currentIndex === 0 ? 'disabled' : '' }}
                        class="px-6 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-semibold disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        Sebelumnya
                    </button>
                    <button wire:click="goToQuestion({{ $currentIndex + 1 }})" {{ $currentIndex === $questions->count() - 1 ? 'disabled' : '' }}
                        class="px-6 py-2 rounded-lg premium-gradient text-white font-semibold shadow-lg shadow-blue-500/20 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        Berikutnya
                    </button>
                </div>
            </div>
        @else
            <div class="glass-panel p-12 rounded-2xl border-white/10 text-center">
                <p class="text-slate-400">Memuat pertanyaan...</p>
            </div>
        @endif
    </div>
</div>
