@extends('layouts.candidate')

@section('header_extra')
    <div class="flex items-center gap-4">
        <div class="hidden sm:flex flex-col items-end">
            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Kandidat</span>
            <span class="text-sm font-semibold text-white">{{ $assignment->candidate->name }}</span>
        </div>
        <div class="w-10 h-10 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center text-blue-400 font-bold">
            {{ strtoupper(substr($assignment->candidate->name, 0, 1)) }}
        </div>
    </div>
@endsection

@section('content')
<div id="exam-app" class="max-w-6xl mx-auto exam-active">
    <div class="glass-panel rounded-3xl shadow-2xl overflow-hidden border border-white/10">
        <!-- Timer Header -->
        <div class="bg-blue-600/20 backdrop-blur-md border-b border-white/5 p-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center border border-blue-500/30">
                        <i class="fas fa-file-alt text-blue-400 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-tight">{{ $assignment->assessment->title }}</h2>
                        <p class="text-slate-400 text-sm font-medium">Pertanyaan <span id="current-question" class="text-blue-400 font-bold">1</span> dari <span id="total-questions" class="text-white">{{ $assignment->assessment->questions->count() }}</span></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-8">
                    <div class="text-center px-6 py-2 bg-slate-900/50 rounded-2xl border border-white/5 min-w-[140px]">
                        <div class="text-3xl font-mono font-black tracking-tighter text-white" id="timer">--:--</div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mt-1">Sisa Waktu</div>
                    </div>
                    
                    <button onclick="submitExam()" class="group bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white px-6 py-3 rounded-2xl border border-rose-500/20 hover:border-rose-500 transition-all duration-300 font-bold text-sm flex items-center gap-2">
                        <span>Selesaikan Ujian</span>
                        <i class="fas fa-check-circle transition-transform group-hover:scale-110"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Question Navigation -->
        <div class="bg-slate-900/30 border-b border-white/5 p-4 overflow-x-auto">
            <div class="flex gap-2 min-w-max pb-2 md:pb-0" id="question-nav">
                @foreach($assignment->assessment->questions as $index => $question)
                    <button onclick="goToQuestion({{ $index }})" class="w-10 h-10 rounded-xl border border-white/10 bg-white/5 text-slate-400 font-bold text-sm hover:border-blue-500/50 hover:bg-blue-500/10 transition-all duration-300 question-nav-btn flex items-center justify-center" data-question="{{ $index }}">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Question Content -->
        <div class="p-8 md:p-12 min-h-[400px]">
            <div id="question-container" class="fade-in">
                <!-- Questions will be loaded here via JavaScript -->
                <div class="flex items-center justify-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="bg-slate-900/30 border-t border-white/5 p-6 flex justify-between items-center">
            <button onclick="previousQuestion()" class="flex items-center gap-2 px-6 py-3 bg-white/5 hover:bg-white/10 text-slate-300 rounded-2xl border border-white/10 transition-all duration-300 disabled:opacity-30 font-bold text-sm" id="prev-btn">
                <i class="fas fa-chevron-left"></i>
                <span>Sebelumnya</span>
            </button>
            <button onclick="nextQuestion()" class="flex items-center gap-2 px-10 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl border border-blue-500/50 transition-all duration-300 font-black text-sm shadow-lg shadow-blue-600/20" id="next-btn">
                <span>Lanjutkan</span>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>


@push('scripts')
<script>
// Exam data
window.examData = @json($assignment->assessment->questions);
window.examToken = '{{ $token }}';
window.examApiUrl = '{{ url('/api') }}';

let currentQuestionIndex = 0;
let answers = {};
let timerInterval;

// Initialize exam
document.addEventListener('DOMContentLoaded', function() {
    loadQuestion(0);
    startTimer();
    loadSavedAnswers();
});

function loadQuestion(index) {
    const question = window.examData[index];
    if (!question) return;

    currentQuestionIndex = index;
    updateQuestionNav();
    updateNavigationButtons();
    
    // Helper to parse option which might be a stringified object
    const parseOption = (opt) => {
        if (typeof opt === 'string' && opt.trim().startsWith('{')) {
            try {
                return JSON.parse(opt);
            } catch (e) {
                return opt;
            }
        }
        return opt;
    };

    let html = `
        <div class="question-item">
            <div class="flex justify-between items-start mb-8">
                <span class="text-sm font-bold text-blue-400 uppercase tracking-widest">Pertanyaan ${index + 1}</span>
                <span class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-4 py-1 rounded-xl text-xs font-bold uppercase tracking-wider">${question.score} Poin</span>
            </div>
            
            <div class="text-2xl font-medium leading-relaxed text-white mb-10">
                ${question.question_text}
            </div>
    `;

    if (question.type === 'multiple_choice') {
        let options = (typeof question.options === 'string') ? JSON.parse(question.options) : question.options;
        html += '<div class="grid grid-cols-1 gap-4">';
        if (Array.isArray(options)) {
            options.forEach(rawOpt => {
                const opt = parseOption(rawOpt);
                const optKey = (typeof opt === 'object' && opt !== null) ? (opt.option || opt.label || '') : opt;
                const optText = (typeof opt === 'object' && opt !== null) ? (opt.text || opt.option_text || '') : opt;
                
                html += `
                    <label class="flex items-center p-5 rounded-2xl border border-white/10 bg-white/5 hover:border-blue-500/50 hover:bg-blue-500/5 cursor-pointer transition-all duration-300 group">
                        <div class="relative flex items-center justify-center w-6 h-6 mr-4">
                            <input type="radio" name="answer" value="${optKey}" ${answers[index] === optKey ? 'checked' : ''} class="peer w-6 h-6 opacity-0 absolute cursor-pointer">
                            <div class="w-6 h-6 border-2 border-white/20 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 transition-all"></div>
                            <div class="w-2 h-2 bg-white rounded-full absolute opacity-0 peer-checked:opacity-100 transition-all"></div>
                        </div>
                        <span class="text-slate-300 group-hover:text-white transition-colors">${optKey ? `<strong class="text-blue-400 mr-2">${optKey}.</strong> ` : ''}${optText}</span>
                    </label>
                `;
            });
        } else if (options && typeof options === 'object') {
            for (const [key, value] of Object.entries(options)) {
                html += `
                    <label class="flex items-center p-5 rounded-2xl border border-white/10 bg-white/5 hover:border-blue-500/50 hover:bg-blue-500/5 cursor-pointer transition-all duration-300 group">
                        <div class="relative flex items-center justify-center w-6 h-6 mr-4">
                            <input type="radio" name="answer" value="${key}" ${answers[index] === key ? 'checked' : ''} class="peer w-6 h-6 opacity-0 absolute cursor-pointer">
                            <div class="w-6 h-6 border-2 border-white/20 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 transition-all"></div>
                            <div class="w-2 h-2 bg-white rounded-full absolute opacity-0 peer-checked:opacity-100 transition-all"></div>
                        </div>
                        <span class="text-slate-300 group-hover:text-white transition-colors"><strong class="text-blue-400 mr-2">${key}.</strong> ${value}</span>
                    </label>
                `;
            }
        }
        html += '</div>';
    } else if (question.type.toLowerCase() === 'essay') {
        html += `
            <textarea name="answer" rows="8" class="w-full p-6 bg-white/5 border border-white/10 rounded-2xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-slate-600" placeholder="Tulis jawaban Anda di sini...">${answers[index] || ''}</textarea>
        `;
    } else if (question.type.toLowerCase() === 'true_false') {
        html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        html += `
            <label class="flex items-center p-5 rounded-2xl border border-white/10 bg-white/5 hover:border-blue-500/50 hover:bg-blue-500/5 cursor-pointer transition-all duration-300 group">
                <input type="radio" name="answer" value="True" ${answers[index] === 'True' ? 'checked' : ''} class="w-6 h-6 text-blue-600 mr-4 border-white/20 bg-transparent">
                <span class="text-slate-300 group-hover:text-white font-bold">BENAR</span>
            </label>
        `;
        html += `
            <label class="flex items-center p-5 rounded-2xl border border-white/10 bg-white/5 hover:border-rose-500/50 hover:bg-rose-500/5 cursor-pointer transition-all duration-300 group">
                <input type="radio" name="answer" value="False" ${answers[index] === 'False' ? 'checked' : ''} class="w-6 h-6 text-rose-600 mr-4 border-white/20 bg-transparent">
                <span class="text-slate-300 group-hover:text-white font-bold">SALAH</span>
            </label>
        `;
        html += '</div>';
    } else if (question.type.toLowerCase() === 'personality') {
        let options = (typeof question.options === 'string') ? JSON.parse(question.options) : question.options;
        html += '<div class="grid grid-cols-1 gap-4">';
        if (Array.isArray(options)) {
            options.forEach(rawOpt => {
                const opt = parseOption(rawOpt);
                const optKey = (typeof opt === 'object' && opt !== null) ? (opt.option || opt.label || opt.value || '') : opt;
                const optText = (typeof opt === 'object' && opt !== null) ? (opt.text || opt.option_text || '') : opt;
                
                html += `
                    <label class="flex items-center p-5 rounded-2xl border border-white/10 bg-white/5 hover:border-purple-500/50 hover:bg-purple-500/5 cursor-pointer transition-all duration-300 group">
                        <div class="relative flex items-center justify-center w-6 h-6 mr-4">
                            <input type="radio" name="answer" value="${optKey}" ${answers[index] === optKey ? 'checked' : ''} class="peer w-6 h-6 opacity-0 absolute cursor-pointer">
                            <div class="w-6 h-6 border-2 border-white/20 rounded-full peer-checked:border-purple-500 peer-checked:bg-purple-500 transition-all"></div>
                            <div class="w-2 h-2 bg-white rounded-full absolute opacity-0 peer-checked:opacity-100 transition-all"></div>
                        </div>
                        <span class="text-slate-300 group-hover:text-white transition-colors">${optKey ? `<strong class="text-purple-400 mr-2">${optKey}.</strong> ` : ''}${optText}</span>
                    </label>
                `;
            });
        } else if (options && typeof options === 'object') {
            for (const [key, value] of Object.entries(options)) {
                html += `
                    <label class="flex items-center p-5 rounded-2xl border border-white/10 bg-white/5 hover:border-purple-500/50 hover:bg-purple-500/5 cursor-pointer transition-all duration-300 group">
                        <div class="relative flex items-center justify-center w-6 h-6 mr-4">
                            <input type="radio" name="answer" value="${key}" ${answers[index] === key ? 'checked' : ''} class="peer w-6 h-6 opacity-0 absolute cursor-pointer">
                            <div class="w-6 h-6 border-2 border-white/20 rounded-full peer-checked:border-purple-500 peer-checked:bg-purple-500 transition-all"></div>
                            <div class="w-2 h-2 bg-white rounded-full absolute opacity-0 peer-checked:opacity-100 transition-all"></div>
                        </div>
                        <span class="text-slate-300 group-hover:text-white transition-colors"><strong class="text-purple-400 mr-2">${key}.</strong> ${value}</span>
                    </label>
                `;
            }
        }
        html += '</div>';
    }


    html += '</div>';
    document.getElementById('question-container').innerHTML = html;
    document.getElementById('current-question').textContent = index + 1;

    // Add event listeners for auto-save
    const inputs = document.querySelectorAll('input[name="answer"], textarea[name="answer"]');
    inputs.forEach(input => {
        input.addEventListener('change', () => saveAnswer(index, input.value));
        input.addEventListener('input', () => saveAnswer(index, input.value));
    });
}

function saveAnswer(questionIndex, answer) {
    answers[questionIndex] = answer;
    localStorage.setItem(`exam_answers_${window.examToken}`, JSON.stringify(answers));
    
    // Auto-save to server
    fetch(`${window.examApiUrl}/exam/${window.examToken}/save-answer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            question_id: window.examData[questionIndex].id,
            answer: answer
        })
    }).catch(err => console.log('Auto-save failed:', err));
}

function loadSavedAnswers() {
    const saved = localStorage.getItem(`exam_answers_${window.examToken}`);
    if (saved) {
        answers = JSON.parse(saved);
    }
}

function updateQuestionNav() {
    document.querySelectorAll('.question-nav-btn').forEach((btn, index) => {
        if (index === currentQuestionIndex) {
            btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            btn.classList.remove('border-gray-300');
        } else if (answers[index]) {
            btn.classList.add('bg-green-100', 'border-green-500');
            btn.classList.remove('border-gray-300');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'bg-green-100', 'border-green-500');
            btn.classList.add('border-gray-300');
        }
    });
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    prevBtn.style.display = currentQuestionIndex === 0 ? 'none' : 'block';
    nextBtn.textContent = currentQuestionIndex === window.examData.length - 1 ? 'Submit' : 'Next';
}

function goToQuestion(index) {
    loadQuestion(index);
}

function nextQuestion() {
    if (currentQuestionIndex < window.examData.length - 1) {
        loadQuestion(currentQuestionIndex + 1);
    } else {
        submitExam();
    }
}

function previousQuestion() {
    if (currentQuestionIndex > 0) {
        loadQuestion(currentQuestionIndex - 1);
    }
}

function startTimer() {
    const duration = window.examData[0]?.assessment?.duration_minutes || 60;
    let timeLeft = duration * 60;
    
    timerInterval = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 300) { // Last 5 minutes
            document.getElementById('timer').classList.add('text-red-500', 'timer-critical');
        }
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            submitExam();
        }
        
        timeLeft--;
    }, 1000);
}

function submitExam() {
    if (!confirm('Apakah Anda yakin ingin submit assessment ini?')) {
        return;
    }
    
    clearInterval(timerInterval);
    
    fetch(`${window.examApiUrl}/exam/${window.examToken}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            answers: answers
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem(`exam_answers_${window.examToken}`);
            window.location.href = `/exam/${window.examToken}/result`;
        } else {
            alert('Gagal submit: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        console.error('Submit error:', err);
        alert('Terjadi kesalahan saat submit. Silakan coba lagi.');
    });
}

// Prevent navigation away
window.addEventListener('beforeunload', (e) => {
    if (Object.keys(answers).length > 0) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endpush
@endsection
