@extends('layouts.app')

@section('title', 'Tambah Barang')
@section('eyebrow', 'Input Inventaris')
@section('page_title', 'Tambah Barang Baru')
@section('page_subtitle', 'Masukkan data barang, lokasi, kategori, dan stok awal agar inventaris langsung bisa dipantau dari dashboard.')

@section('page_actions')
    <a class="button-secondary" href="{{ route('barang.index') }}">Lihat Daftar</a>
@endsection

@section('content')
    @if ($canUseAssistant)
        <section class="panel section-card" style="margin-bottom: 18px;">
            <div class="section-header">
                <div>
                    <div class="muted">AI assistant</div>
                    <h3 class="section-title">Tambah Barang Dengan Chat atau Suara</h3>
                </div>
            </div>

            <div class="assistant-grid">
                <div class="assistant-copy">
                    <p class="muted">
                        Ucapkan atau ketik perintah natural. Assistant akan membaca data, lalu langsung
                        menyimpan barang ke database kalau informasinya cukup.
                    </p>
                    <div class="assistant-example">
                        Contoh:
                        <code>Tambah barang Spidol Whiteboard kategori ATK lokasi Gudang Utama satuan pcs minimum stok lima stok baik dua puluh deskripsi untuk kelas</code>
                    </div>
                    <div class="assistant-chip-row">
                        <span class="assistant-chip">Voice input</span>
                        <span class="assistant-chip">Text to speech</span>
                        <span class="assistant-chip">Simpan ke DB</span>
                    </div>
                </div>

                <div class="assistant-shell" data-ai-item-assistant>
                    <div class="assistant-log" data-assistant-log>
                        <div class="assistant-bubble assistant-bubble-assistant">
                            Halo, sebutkan data barang seperti nama, SKU, kategori, lokasi, satuan, minimum stok, dan stok awal.
                        </div>
                    </div>

                    <label class="field" for="assistant-message">
                        <span class="field-label">Perintah assistant</span>
                        <textarea
                            class="textarea assistant-input"
                            id="assistant-message"
                            data-assistant-input
                            rows="4"
                            placeholder="Contoh: tambah barang Kertas HVS kategori ATK lokasi Gudang Utama satuan rim minimum stok lima stok baik sepuluh stok kurang baik dua"
                        ></textarea>
                    </label>

                    <div class="assistant-actions">
                        <button class="button-secondary" type="button" data-assistant-mic>
                            Mulai Mic
                        </button>
                        <button class="button" type="button" data-assistant-send>
                            Simpan Lewat Assistant
                        </button>
                    </div>

                    <p class="assistant-status muted" data-assistant-status>
                        Hasil voice-to-text akan langsung diproses setelah rekaman selesai. Balasan assistant akan dibacakan kalau text-to-speech tersedia.
                    </p>
                </div>
            </div>
        </section>
    @else
        <section class="panel section-card notice-box" style="margin-bottom: 18px;">
            <strong>AI assistant belum aktif untuk akun ini.</strong>
            <p style="margin: 8px 0 0;">Gunakan form manual di bawah, atau minta superadmin mengaktifkan akses `AI Barang` untuk role Anda.</p>
        </section>
    @endif

    <section class="panel section-card">
        <div class="section-header">
            <div>
                <div class="muted">Form barang</div>
                <h3 class="section-title">Data Inventaris Baru</h3>
            </div>
        </div>

        <form method="POST" action="{{ route('barang.store') }}">
            @csrf

            @include('items._form')

            <div class="button-row" style="margin-top: 18px;">
                <button class="button" type="submit">Simpan Barang</button>
                <a class="button-ghost" href="{{ route('barang.index') }}">Batal</a>
            </div>
        </form>
    </section>

    @if ($canUseAssistant)
        <script>
            (() => {
                const root = document.querySelector('[data-ai-item-assistant]');

                if (!root) {
                    return;
                }

                const input = root.querySelector('[data-assistant-input]');
                const sendButton = root.querySelector('[data-assistant-send]');
                const micButton = root.querySelector('[data-assistant-mic]');
                const log = root.querySelector('[data-assistant-log]');
                const status = root.querySelector('[data-assistant-status]');
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                let recognition = null;
                let isListening = false;
                let transcriptBase = '';
                let finalTranscript = '';
                let interimTranscript = '';
                let shouldAutoSubmit = false;

                const speak = (text) => {
                    if (!('speechSynthesis' in window) || !text) {
                        return;
                    }

                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'id-ID';
                    window.speechSynthesis.speak(utterance);
                };

                const addBubble = (text, role) => {
                    const bubble = document.createElement('div');
                    bubble.className = `assistant-bubble assistant-bubble-${role}`;
                    bubble.textContent = text;
                    log.appendChild(bubble);
                    log.scrollTop = log.scrollHeight;
                };

                const syncTranscript = () => {
                    input.value = [transcriptBase, finalTranscript, interimTranscript]
                        .filter(Boolean)
                        .join(' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                };

                const setBusy = (busy) => {
                    sendButton.disabled = busy;
                    micButton.disabled = busy;
                };

                const sendMessage = async () => {
                    const message = input.value.trim();

                    if (!message) {
                        status.textContent = 'Tulis atau ucapkan dulu perintah barangnya.';
                        input.focus();
                        return;
                    }

                    addBubble(message, 'user');
                    status.textContent = 'Assistant sedang memproses dan menyimpan ke database...';
                    setBusy(true);

                    try {
                        const response = await fetch(@json(route('ai.barang-chat')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify({ message }),
                        });

                        const payload = await response.json();
                        const lines = [payload.message]
                            .concat(payload.summary || [])
                            .concat(payload.warnings || []);
                        const assistantText = lines.filter(Boolean).join(' ');

                        addBubble(assistantText, 'assistant');
                        status.textContent = payload.message || 'Assistant sudah merespons.';
                        speak(payload.message || assistantText);

                        if (payload.ok && payload.redirect_url) {
                            status.textContent = 'Barang berhasil dibuat. Mengalihkan ke detail barang...';
                            input.value = '';
                            transcriptBase = '';
                            finalTranscript = '';
                            interimTranscript = '';
                            window.setTimeout(() => {
                                window.location.href = payload.redirect_url;
                            }, 1200);
                        }
                    } catch (error) {
                        const fallbackMessage = 'Assistant gagal memproses permintaan. Coba lagi dengan format yang lebih lengkap.';
                        addBubble(fallbackMessage, 'assistant');
                        status.textContent = fallbackMessage;
                        speak(fallbackMessage);
                    } finally {
                        setBusy(false);
                    }
                };

                sendButton.addEventListener('click', sendMessage);

                input.addEventListener('keydown', (event) => {
                    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                        event.preventDefault();
                        sendMessage();
                    }
                });

                if (!SpeechRecognition) {
                    micButton.disabled = true;
                    micButton.textContent = 'Mic Tidak Didukung';
                    status.textContent = 'Browser ini tidak mendukung speech-to-text. Chat teks tetap bisa dipakai.';
                    return;
                }

                recognition = new SpeechRecognition();
                recognition.lang = 'id-ID';
                recognition.interimResults = true;
                recognition.continuous = false;

                recognition.addEventListener('result', (event) => {
                    finalTranscript = Array.from(event.results)
                        .filter((result) => result.isFinal)
                        .map((result) => result[0]?.transcript || '')
                        .join(' ')
                        .trim();

                    interimTranscript = Array.from(event.results)
                        .filter((result) => !result.isFinal)
                        .map((result) => result[0]?.transcript || '')
                        .join(' ')
                        .trim();

                    syncTranscript();
                    status.textContent = finalTranscript
                        ? 'Transkrip suara tertangkap. Assistant akan menyimpan barang setelah rekaman selesai.'
                        : 'Assistant sedang mendengarkan...';
                });

                recognition.addEventListener('start', () => {
                    isListening = true;
                    transcriptBase = input.value.trim();
                    finalTranscript = '';
                    interimTranscript = '';
                    shouldAutoSubmit = true;
                    micButton.textContent = 'Stop Mic';
                    status.textContent = 'Assistant sedang mendengarkan...';
                });

                recognition.addEventListener('end', () => {
                    isListening = false;
                    micButton.textContent = 'Mulai Mic';
                    syncTranscript();

                    if (shouldAutoSubmit && finalTranscript && !sendButton.disabled) {
                        status.textContent = 'Suara berhasil ditangkap. Assistant sedang menyimpan barang...';
                        shouldAutoSubmit = false;
                        sendMessage();
                        return;
                    }

                    shouldAutoSubmit = false;

                    if (input.value.trim()) {
                        status.textContent = 'Transkrip suara sudah masuk. Anda bisa rekam lagi atau kirim manual.';
                    }
                });

                recognition.addEventListener('error', () => {
                    shouldAutoSubmit = false;
                    status.textContent = 'Gagal membaca suara. Ulangi lagi atau pakai chat teks.';
                });

                micButton.addEventListener('click', () => {
                    if (isListening) {
                        recognition.stop();
                        return;
                    }

                    recognition.start();
                });
            })();
        </script>
    @endif
@endsection
