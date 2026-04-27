@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page_title', 'Profil Saya')
@section('page_subtitle', 'Kelola akun sendiri tanpa mencampur pengaturan admin dan superadmin.')

@section('page_actions')
    <a class="btn btn-secondary" href="{{ route($user->accessibleHomeRoute()) }}">Kembali</a>
@endsection

@section('content')
    <section class="profile-grid">
        <article class="table-panel">
            <div class="table-panel-header">
                <div>
                    <h3 class="panel-title">Informasi Akun</h3>
                    <p class="panel-subtitle">Perbarui nama tampilan dan foto profil yang muncul di aplikasi.</p>
                </div>
                <span class="badge badge-success">{{ $user->roleLabel() }}</span>
            </div>

            <div class="profile-panel-body">
                <div class="profile-summary">
                    @if ($user->profilePhotoUrl())
                        <img class="profile-summary__avatar" src="{{ $user->profilePhotoUrl() }}" alt="Foto profil {{ $user->name }}">
                    @else
                        <div class="profile-summary__avatar profile-summary__avatar--fallback">{{ $user->initials() }}</div>
                    @endif

                    <div class="profile-summary__copy">
                        <h4>{{ $user->name }}</h4>
                        <p>{{ $user->email }}</p>
                        <span>{{ $user->roleLabel() }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Nama</label>
                            <input class="input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" required>
                        </div>

                        <div class="field">
                            <label for="email">Email</label>
                            <input class="input" id="email" type="email" value="{{ $user->email }}" disabled>
                            <small>Email login ditampilkan sebagai referensi dan tidak diubah dari halaman ini.</small>
                        </div>

                        <div class="field-wide">
                            <label for="profile_photo">Foto Profil</label>
                            <div class="profile-photo-field">
                                <div class="profile-photo-preview-card">
                                    <div class="profile-photo-preview-frame">
                                        @if ($user->profilePhotoUrl())
                                            <img
                                                id="profile-photo-preview-image"
                                                class="profile-photo-preview-image"
                                                src="{{ $user->profilePhotoUrl() }}"
                                                alt="Preview foto profil {{ $user->name }}"
                                            >
                                            <div id="profile-photo-preview-fallback" class="profile-photo-preview-fallback" hidden>{{ $user->initials() }}</div>
                                        @else
                                            <img
                                                id="profile-photo-preview-image"
                                                class="profile-photo-preview-image"
                                                src=""
                                                alt="Preview foto profil {{ $user->name }}"
                                                hidden
                                            >
                                            <div id="profile-photo-preview-fallback" class="profile-photo-preview-fallback">{{ $user->initials() }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>Preview Foto</strong>
                                        <p id="profile-photo-status" class="profile-photo-status">Pilih foto lalu atur crop sebelum disimpan.</p>
                                    </div>
                                </div>

                                <input class="input" id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            </div>
                            <small>Format yang didukung: JPG, JPEG, PNG, WEBP. Maksimal 2 MB. Setelah pilih foto, Anda bisa crop dulu.</small>
                        </div>
                    </div>

                    <div class="button-row profile-panel-actions">
                        <button class="btn btn-primary" type="submit">Simpan Profil</button>
                    </div>
                </form>
            </div>
        </article>

        <article class="table-panel">
            <div class="table-panel-header">
                <div>
                    <h3 class="panel-title">Keamanan Akun</h3>
                    <p class="panel-subtitle">Ganti password dengan memasukkan password lama Anda terlebih dahulu.</p>
                </div>
            </div>

            <div class="profile-panel-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="field-wide">
                            <label for="current_password">Password Lama</label>
                            <input class="input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                        </div>

                        <div class="field">
                            <label for="password">Password Baru</label>
                            <input class="input" id="password" name="password" type="password" autocomplete="new-password" required>
                        </div>

                        <div class="field">
                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                            <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        </div>
                    </div>

                    <div class="button-row profile-panel-actions">
                        <button class="btn btn-primary" type="submit">Perbarui Password</button>
                    </div>
                </form>
            </div>
        </article>

        @if ($canManageAccounts || $canManageAccess)
            <article class="table-panel profile-panel-full">
                <div class="table-panel-header">
                    <div>
                        <h3 class="panel-title">Pengaturan Lanjutan</h3>
                        <p class="panel-subtitle">Menu admin dan superadmin dipisah ke halaman khusus agar lebih rapi.</p>
                    </div>
                </div>

                <div class="profile-panel-body">
                    <div class="profile-link-grid">
                        @if ($canManageAccounts)
                            <a class="profile-link-card" href="{{ route('settings.accounts') }}">
                                <strong>Akun Management</strong>
                                <p>Kelola akun yang bisa dibuat, diubah, diberi role, dan di-reset password-nya.</p>
                                <span>Buka halaman akun</span>
                            </a>
                        @endif

                        @if ($canManageAccess)
                            <a class="profile-link-card" href="{{ route('settings.access') }}">
                                <strong>Akses Management</strong>
                                <p>Atur modul apa saja yang bisa diakses oleh role `Pengguna` dan `Admin`.</p>
                                <span>Buka halaman akses</span>
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @endif
    </section>

    <div id="profile-crop-modal" class="profile-crop-modal" hidden>
        <div class="profile-crop-backdrop" data-crop-close></div>
        <div class="profile-crop-dialog">
            <div class="profile-crop-header">
                <div>
                    <h3>Crop Foto Profil</h3>
                    <p>Geser foto dan atur zoom untuk menentukan area profil.</p>
                </div>
                <button class="button-ghost" type="button" data-crop-close>Tutup</button>
            </div>

            <div class="profile-crop-layout">
                <div class="profile-crop-stage-wrap">
                    <div id="profile-crop-stage" class="profile-crop-stage">
                        <img id="profile-crop-image" class="profile-crop-image" src="" alt="Crop foto profil">
                    </div>
                </div>

                <div class="profile-crop-sidebar">
                    <div class="profile-section-copy" style="margin-bottom: 0;">
                        <h4>Preview Hasil</h4>
                        <p>Area kotak ini yang akan dipakai sebagai foto profil baru.</p>
                    </div>

                    <div class="profile-crop-preview-shell">
                        <canvas id="profile-crop-preview-canvas" class="profile-crop-preview-canvas" width="220" height="220"></canvas>
                    </div>

                    <div class="field">
                        <label for="profile-crop-zoom">Zoom</label>
                        <input id="profile-crop-zoom" class="input" type="range" min="1" max="3" step="0.01" value="1">
                    </div>

                    <div class="profile-crop-actions">
                        <button id="profile-crop-apply" class="btn btn-primary" type="button">Gunakan Crop</button>
                        <button class="btn btn-secondary" type="button" data-crop-close>Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('profile_photo');
        const modal = document.getElementById('profile-crop-modal');
        const cropImage = document.getElementById('profile-crop-image');
        const cropStage = document.getElementById('profile-crop-stage');
        const zoomInput = document.getElementById('profile-crop-zoom');
        const applyButton = document.getElementById('profile-crop-apply');
        const previewCanvas = document.getElementById('profile-crop-preview-canvas');
        const previewContext = previewCanvas.getContext('2d');
        const previewImage = document.getElementById('profile-photo-preview-image');
        const previewFallback = document.getElementById('profile-photo-preview-fallback');
        const status = document.getElementById('profile-photo-status');

        if (! input || ! modal || ! cropImage || ! cropStage || !zoomInput || !applyButton || !previewCanvas || !previewContext) {
            return;
        }

        const state = {
            activeFile: null,
            objectUrl: null,
            naturalWidth: 0,
            naturalHeight: 0,
            scale: 1,
            baseScale: 1,
            offsetX: 0,
            offsetY: 0,
            dragging: false,
            dragStartX: 0,
            dragStartY: 0,
            dragOriginX: 0,
            dragOriginY: 0,
        };

        const stageSize = () => cropStage.clientWidth || 320;

        const revokeObjectUrl = () => {
            if (state.objectUrl) {
                URL.revokeObjectURL(state.objectUrl);
                state.objectUrl = null;
            }
        };

        const clampOffsets = () => {
            const frame = stageSize();
            const renderedWidth = state.naturalWidth * state.scale;
            const renderedHeight = state.naturalHeight * state.scale;
            const maxOffsetX = Math.max((renderedWidth - frame) / 2, 0);
            const maxOffsetY = Math.max((renderedHeight - frame) / 2, 0);

            state.offsetX = Math.min(Math.max(state.offsetX, -maxOffsetX), maxOffsetX);
            state.offsetY = Math.min(Math.max(state.offsetY, -maxOffsetY), maxOffsetY);
        };

        const renderCropImage = () => {
            cropImage.style.transform = `translate(calc(-50% + ${state.offsetX}px), calc(-50% + ${state.offsetY}px)) scale(${state.scale})`;
        };

        const drawPreview = () => {
            const frame = stageSize();
            const sourceX = ((state.naturalWidth / 2) - (frame / (2 * state.scale))) - (state.offsetX / state.scale);
            const sourceY = ((state.naturalHeight / 2) - (frame / (2 * state.scale))) - (state.offsetY / state.scale);
            const sourceSize = frame / state.scale;

            previewContext.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
            previewContext.drawImage(
                cropImage,
                sourceX,
                sourceY,
                sourceSize,
                sourceSize,
                0,
                0,
                previewCanvas.width,
                previewCanvas.height
            );
        };

        const render = () => {
            clampOffsets();
            renderCropImage();
            drawPreview();
        };

        const openModal = () => {
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
        };

        const closeModal = ({ resetInput = false } = {}) => {
            modal.hidden = true;
            document.body.style.overflow = '';
            state.dragging = false;

            if (resetInput) {
                input.value = '';
            }

            revokeObjectUrl();
        };

        const syncPreview = (blob) => {
            const previewUrl = URL.createObjectURL(blob);

            previewImage.src = previewUrl;
            previewImage.hidden = false;
            previewFallback.hidden = true;
            status.textContent = 'Foto sudah dicrop dan siap disimpan.';

            previewImage.onload = () => URL.revokeObjectURL(previewUrl);
        };

        const replaceInputFile = (blob) => {
            const extension = blob.type === 'image/jpeg' ? 'jpg' : 'png';
            const croppedFile = new File([blob], `profile-crop.${extension}`, { type: blob.type });
            const dataTransfer = new DataTransfer();

            dataTransfer.items.add(croppedFile);
            input.files = dataTransfer.files;
        };

        input.addEventListener('change', (event) => {
            const [file] = event.target.files || [];

            if (! file) {
                return;
            }

            revokeObjectUrl();
            state.activeFile = file;
            state.objectUrl = URL.createObjectURL(file);
            cropImage.src = state.objectUrl;
            zoomInput.value = '1';
        });

        cropImage.addEventListener('load', () => {
            openModal();
            window.requestAnimationFrame(() => {
                state.naturalWidth = cropImage.naturalWidth;
                state.naturalHeight = cropImage.naturalHeight;
                state.baseScale = Math.max(stageSize() / state.naturalWidth, stageSize() / state.naturalHeight);
                state.scale = state.baseScale;
                state.offsetX = 0;
                state.offsetY = 0;
                render();
            });
        });

        zoomInput.addEventListener('input', () => {
            state.scale = state.baseScale * Number(zoomInput.value);
            render();
        });

        cropStage.addEventListener('pointerdown', (event) => {
            if (! state.activeFile) {
                return;
            }

            state.dragging = true;
            state.dragStartX = event.clientX;
            state.dragStartY = event.clientY;
            state.dragOriginX = state.offsetX;
            state.dragOriginY = state.offsetY;
            cropStage.setPointerCapture(event.pointerId);
        });

        cropStage.addEventListener('pointermove', (event) => {
            if (! state.dragging) {
                return;
            }

            state.offsetX = state.dragOriginX + (event.clientX - state.dragStartX);
            state.offsetY = state.dragOriginY + (event.clientY - state.dragStartY);
            render();
        });

        const stopDragging = (event) => {
            if (! state.dragging) {
                return;
            }

            state.dragging = false;

            if (event.pointerId !== undefined && cropStage.hasPointerCapture(event.pointerId)) {
                cropStage.releasePointerCapture(event.pointerId);
            }
        };

        cropStage.addEventListener('pointerup', stopDragging);
        cropStage.addEventListener('pointercancel', stopDragging);

        applyButton.addEventListener('click', () => {
            previewCanvas.toBlob((blob) => {
                if (! blob) {
                    return;
                }

                replaceInputFile(blob);
                syncPreview(blob);
                closeModal();
            }, 'image/png');
        });

        modal.querySelectorAll('[data-crop-close]').forEach((element) => {
            element.addEventListener('click', () => closeModal({ resetInput: true }));
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && ! modal.hidden) {
                closeModal({ resetInput: true });
            }
        });

        window.addEventListener('resize', () => {
            if (! modal.hidden && state.activeFile) {
                state.baseScale = Math.max(stageSize() / state.naturalWidth, stageSize() / state.naturalHeight);
                state.scale = Math.max(state.scale, state.baseScale);
                render();
            }
        });
    })();
</script>
@endpush
