<div id="ai-chat-widget" class="ai-chat-widget">
    <!-- Chat Toggle Button -->
    <button id="ai-chat-toggle" class="ai-chat-toggle" title="Tanya Gemini AI">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
        <span class="ai-chat-badge">Gemini</span>
    </button>

    <!-- Chat Window Popup -->
    <div id="ai-chat-window" class="ai-chat-window" style="display: none;">
        <div class="ai-chat-header">
            <div class="ai-chat-header-info">
                <div class="ai-chat-avatar">G</div>
                <div>
                    <strong>Gemini Assistant</strong>
                    <span class="ai-chat-status">Online</span>
                </div>
            </div>
            <button id="ai-chat-close" class="ai-chat-close">&times;</button>
        </div>

        <div class="ai-chat-messages" id="ai-chat-messages">
            <div class="assistant-bubble assistant-bubble-assistant">
                Halo! Saya Gemini. Ada yang bisa saya bantu terkait inventaris hari ini?
            </div>
        </div>

        <div class="ai-chat-input-area">
            <textarea id="ai-chat-input" placeholder="Tanya sesuatu..." rows="1"></textarea>
            <div class="ai-chat-actions">
                <button id="ai-chat-mic" class="ai-chat-btn-icon" title="Voice Input">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-20a3 3 0 00-3 3v10a3 3 0 006 0V6a3 3 0 00-3-3z"></path></svg>
                </button>
                <button id="ai-chat-send" class="ai-chat-btn-send">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </div>
            <p id="ai-chat-status" class="ai-chat-status-text">Gemini AI Assistant</p>
        </div>
    </div>
</div>

<style>
    .ai-chat-widget {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
        font-family: inherit;
        touch-action: none;
    }

    .ai-chat-toggle {
        width: 60px;
        height: 60px;
        border-radius: 30px;
        background: var(--accent);
        color: white;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        position: relative;
        touch-action: none;
        user-select: none;
    }

    .ai-chat-toggle:hover {
        transform: scale(1.05);
        background: var(--accent-dark, #3b82f6);
    }

    .ai-chat-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--danger);
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .ai-chat-window {
        position: absolute;
        bottom: 75px;
        right: 0;
        width: 380px;
        height: 550px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .ai-chat-header {
        background: var(--accent);
        color: white;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: move;
        touch-action: none;
        user-select: none;
    }

    .ai-chat-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ai-chat-avatar {
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
    }

    .ai-chat-status {
        display: block;
        font-size: 11px;
        opacity: 0.8;
    }

    .ai-chat-close {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        opacity: 0.7;
    }

    .ai-chat-close:hover {
        opacity: 1;
    }

    .ai-chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f9fafb;
    }

    .assistant-bubble {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.5;
    }

    .assistant-bubble-user {
        align-self: flex-end;
        background: var(--accent);
        color: white;
        border-bottom-right-radius: 2px;
    }

    .assistant-bubble-assistant {
        align-self: flex-start;
        background: white;
        color: var(--text-main);
        border-bottom-left-radius: 2px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .ai-chat-input-area {
        padding: 16px;
        background: white;
        border-top: 1px solid #f3f4f6;
    }

    #ai-chat-input {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
        resize: none;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }

    #ai-chat-input:focus {
        border-color: var(--accent);
    }

    .ai-chat-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        align-items: center;
    }

    .ai-chat-btn-icon {
        background: #f3f4f6;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-muted);
        transition: all 0.2s;
    }

    .ai-chat-btn-icon:hover {
        background: #e5e7eb;
        color: var(--text-main);
    }

    .ai-chat-btn-send {
        background: var(--accent);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: background 0.2s;
    }

    .ai-chat-btn-send:hover {
        background: var(--accent-dark, #3b82f6);
    }

    .ai-chat-status-text {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 8px;
        text-align: center;
    }

    @media (max-width: 480px) {
        .ai-chat-widget {
            right: 16px;
            bottom: calc(82px + env(safe-area-inset-bottom));
        }

        .ai-chat-window {
            width: calc(100vw - 32px);
            height: calc(100vh - 160px);
            right: -8px;
        }
    }
</style>

<script>
    (function() {
        const toggle = document.getElementById('ai-chat-toggle');
        const windowEl = document.getElementById('ai-chat-window');
        const closeBtn = document.getElementById('ai-chat-close');
        const input = document.getElementById('ai-chat-input');
        const sendBtn = document.getElementById('ai-chat-send');
        const messages = document.getElementById('ai-chat-messages');
        const status = document.getElementById('ai-chat-status');
        const micBtn = document.getElementById('ai-chat-mic');
        const widget = document.getElementById('ai-chat-widget');
        const header = windowEl.querySelector('.ai-chat-header');
        const storageKey = 'ai-chat-widget-position';

        let isListening = false;
        let recognition = null;
        let isDragging = false;
        let didDrag = false;
        let dragStart = null;
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

        const setWidgetPosition = (left, top, save = true) => {
            const size = toggle.getBoundingClientRect();
            const nextLeft = clamp(left, 8, window.innerWidth - size.width - 8);
            const nextTop = clamp(top, 8, window.innerHeight - size.height - 8);

            widget.style.left = `${nextLeft}px`;
            widget.style.top = `${nextTop}px`;
            widget.style.right = 'auto';
            widget.style.bottom = 'auto';

            if (save) {
                localStorage.setItem(storageKey, JSON.stringify({ left: nextLeft, top: nextTop }));
            }

            placeWindow();
        };

        const placeWindow = () => {
            if (windowEl.style.display === 'none') return;

            windowEl.style.position = 'fixed';
            windowEl.style.right = 'auto';
            windowEl.style.bottom = 'auto';

            const buttonRect = toggle.getBoundingClientRect();
            const windowRect = windowEl.getBoundingClientRect();
            const margin = 12;
            const gap = 12;
            const preferredLeft = buttonRect.right - windowRect.width;
            const preferredTop = buttonRect.top - windowRect.height - gap;
            const fallbackTop = buttonRect.bottom + gap;

            windowEl.style.left = `${clamp(preferredLeft, margin, window.innerWidth - windowRect.width - margin)}px`;
            windowEl.style.top = `${clamp(preferredTop < margin ? fallbackTop : preferredTop, margin, window.innerHeight - windowRect.height - margin)}px`;
        };

        const restorePosition = () => {
            try {
                const saved = JSON.parse(localStorage.getItem(storageKey));
                if (saved && Number.isFinite(saved.left) && Number.isFinite(saved.top)) {
                    setWidgetPosition(saved.left, saved.top, false);
                }
            } catch (error) {
                localStorage.removeItem(storageKey);
            }
        };

        const startDrag = (event) => {
            if (event.button !== undefined && event.button !== 0) return;
            if (event.currentTarget === header && event.target.closest('button')) return;

            const rect = widget.getBoundingClientRect();
            isDragging = true;
            didDrag = false;
            dragStart = {
                pointerId: event.pointerId,
                clientX: event.clientX,
                clientY: event.clientY,
                left: rect.left,
                top: rect.top,
            };

            event.currentTarget.setPointerCapture?.(event.pointerId);
        };

        const moveDrag = (event) => {
            if (!isDragging || !dragStart || event.pointerId !== dragStart.pointerId) return;

            const deltaX = event.clientX - dragStart.clientX;
            const deltaY = event.clientY - dragStart.clientY;

            if (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4) {
                didDrag = true;
            }

            setWidgetPosition(dragStart.left + deltaX, dragStart.top + deltaY);
        };

        const endDrag = (event) => {
            if (!isDragging || !dragStart || event.pointerId !== dragStart.pointerId) return;
            isDragging = false;
            dragStart = null;
            event.currentTarget.releasePointerCapture?.(event.pointerId);
        };

        [toggle, header].forEach((handle) => {
            handle.addEventListener('pointerdown', startDrag);
            handle.addEventListener('pointermove', moveDrag);
            handle.addEventListener('pointerup', endDrag);
            handle.addEventListener('pointercancel', endDrag);
        });

        toggle.addEventListener('click', () => {
            if (didDrag) {
                didDrag = false;
                return;
            }

            const isVisible = windowEl.style.display !== 'none';
            windowEl.style.display = isVisible ? 'none' : 'flex';
            if (!isVisible) {
                placeWindow();
                input.focus();
            }
        });

        closeBtn.addEventListener('click', () => {
            windowEl.style.display = 'none';
        });

        window.addEventListener('resize', () => {
            const rect = widget.getBoundingClientRect();
            setWidgetPosition(rect.left, rect.top, false);
            placeWindow();
        });

        const addBubble = (text, role) => {
            const bubble = document.createElement('div');
            bubble.className = `assistant-bubble assistant-bubble-${role}`;
            bubble.textContent = text;
            messages.appendChild(bubble);
            messages.scrollTop = messages.scrollHeight;
        };

        const sendMessage = async () => {
            const message = input.value.trim();
            if (!message) return;

            input.value = '';
            addBubble(message, 'user');
            status.textContent = 'Gemini sedang berpikir...';
            sendBtn.disabled = true;

            try {
                const response = await fetch('{{ route('ai.barang-chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message }),
                });

                const payload = await response.json();
                addBubble(payload.message, 'assistant');
                status.textContent = 'Gemini AI Assistant';
                
                if (payload.ok && payload.redirect_url) {
                    addBubble("Mengalihkan Anda ke halaman yang relevan...", 'assistant');
                    setTimeout(() => {
                        window.location.href = payload.redirect_url;
                    }, 2000);
                }
            } catch (error) {
                addBubble('Maaf, gagal menghubungi Gemini. Coba lagi nanti.', 'assistant');
                status.textContent = 'Terjadi kesalahan.';
            } finally {
                sendBtn.disabled = false;
            }
        };

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Mic logic
        if (SpeechRecognition) {
            recognition = new SpeechRecognition();
            recognition.lang = 'id-ID';
            recognition.interimResults = false;

            recognition.onstart = () => {
                isListening = true;
                micBtn.style.color = 'var(--danger)';
                status.textContent = 'Mendengarkan...';
            };

            recognition.onend = () => {
                isListening = false;
                micBtn.style.color = '';
                status.textContent = 'Gemini AI Assistant';
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                input.value = transcript;
                sendMessage();
            };

            micBtn.addEventListener('click', () => {
                if (isListening) {
                    recognition.stop();
                } else {
                    recognition.start();
                }
            });
        } else {
            micBtn.style.display = 'none';
        }

        restorePosition();
    })();
</script>
