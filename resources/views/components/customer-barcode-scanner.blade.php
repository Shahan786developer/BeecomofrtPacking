<!-- Reusable Customer Camera Barcode Scanner Modal & Handler -->
<div class="modal fade" id="customerBarcodeScannerModal" tabindex="-1" aria-labelledby="customerBarcodeScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fs-6 fw-bold mb-0" id="customerBarcodeScannerModalLabel">
                    <i class="bi bi-camera me-2 text-warning"></i> Scan Customer Barcode (Camera)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <div class="alert alert-info py-2 small mb-3 text-start">
                    <i class="bi bi-info-circle me-1"></i> Point your mobile or webcam at the customer's 32&times;22mm barcode sticker.
                </div>

                <!-- Video Viewport Container -->
                <div id="customer-barcode-reader" style="width: 100%; min-height: 250px; background: #0f172a; border-radius: 8px; overflow: hidden;" class="mx-auto position-relative">
                    <div id="scanner-loading-spinner" class="position-absolute top-50 start-50 translate-middle text-white text-center">
                        <div class="spinner-border text-light mb-2" role="status"></div>
                        <div class="small fw-semibold">Accessing Camera...</div>
                    </div>
                </div>

                <!-- Scanner Status Message -->
                <div id="scanner-status" class="small text-muted mt-2 fw-semibold">
                    Align customer barcode within frame
                </div>

                <!-- Manual / USB Scanner Fallback Input -->
                <div class="input-group input-group-sm mt-3">
                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" id="manual-customer-barcode-input" class="form-control font-monospace" placeholder="Or enter code / ID (e.g. B001)..." autocomplete="off">
                    <button type="button" id="btn-manual-customer-apply" class="btn btn-primary fw-bold">Select</button>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light d-flex justify-content-between align-items-center">
                <span class="small text-muted"><i class="bi bi-check2-circle text-success me-1"></i> Instant match &amp; auto-close</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        let activeSelectTargetId = 'customer_id';
        let html5QrCodeScanner = null;
        let isScanningActive = false;

        // Pleasant Beep Confirmation Synthesizer
        function playConfirmBeep() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = "sine";
                osc.frequency.setValueAtTime(980, ctx.currentTime); // High friendly ping
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.16);
                osc.start();
                osc.stop(ctx.currentTime + 0.16);
            } catch (e) {
                // AudioContext autoplay restriction fallback
            }
        }

        // Match scanned code against customer select options
        function matchAndSelectCustomer(scannedRaw) {
            if (!scannedRaw) return false;
            const targetSelect = document.getElementById(activeSelectTargetId);
            if (!targetSelect) {
                console.warn('Target customer select not found:', activeSelectTargetId);
                return false;
            }

            const cleanCode = scannedRaw.trim().toUpperCase();
            let matchedOption = null;

            // 1. Search options directly
            for (let i = 0; i < targetSelect.options.length; i++) {
                const opt = targetSelect.options[i];
                if (!opt.value) continue;

                const optCode = (opt.getAttribute('data-code') || '').trim().toUpperCase();
                const optId = String(opt.value).trim();
                const optText = opt.textContent.toUpperCase();

                // Match exact code (e.g. B001), exact ID (e.g. 1), or contained code
                if (optCode && (optCode === cleanCode || cleanCode.includes(optCode))) {
                    matchedOption = opt;
                    break;
                }
                if (optId && cleanCode === optId) {
                    matchedOption = opt;
                    break;
                }
                if (cleanCode.startsWith('B') && cleanCode.substring(1) == optId) {
                    matchedOption = opt;
                    break;
                }
                if (cleanCode.startsWith('CUST') && cleanCode.replace(/[^0-9]/g, '') == optId) {
                    matchedOption = opt;
                    break;
                }
                if (optText.includes('[' + cleanCode + ']') || optText.includes('(' + cleanCode + ')')) {
                    matchedOption = opt;
                    break;
                }
            }

            if (matchedOption) {
                playConfirmBeep();

                const matchedValue = matchedOption.value;

                // Update select or TomSelect
                if (targetSelect.tomselect) {
                    targetSelect.tomselect.setValue(matchedValue, false);
                } else {
                    targetSelect.value = matchedValue;
                }

                // Trigger change event to fire balance updates and price calculations
                targetSelect.dispatchEvent(new Event('change', { bubbles: true }));

                // Close Scanner Modal
                const modalEl = document.getElementById('customerBarcodeScannerModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Optional Toast/Status notice
                const statusEl = document.getElementById('scanner-status');
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Customer Matched: ' + matchedOption.textContent + '</span>';
                }

                return true;
            }

            return false;
        }

        // Camera barcode scanner lifecycle
        function startCameraScanner() {
            const statusEl = document.getElementById('scanner-status');
            const spinner = document.getElementById('scanner-loading-spinner');

            if (typeof Html5Qrcode === 'undefined') {
                if (statusEl) statusEl.innerHTML = '<span class="text-danger">Camera library not loaded. Use manual input below.</span>';
                if (spinner) spinner.style.display = 'none';
                return;
            }

            if (!html5QrCodeScanner) {
                html5QrCodeScanner = new Html5Qrcode("customer-barcode-reader");
            }

            if (spinner) spinner.style.display = 'block';
            if (statusEl) statusEl.textContent = 'Opening camera...';

            const qrConfig = {
                fps: 15,
                qrbox: { width: 260, height: 160 },
                aspectRatio: 1.333334
            };

            html5QrCodeScanner.start(
                { facingMode: "environment" }, // Prefer back camera on mobile
                qrConfig,
                function(decodedText) {
                    if (!isScanningActive) return;
                    console.log("Barcode scanned:", decodedText);
                    const matched = matchAndSelectCustomer(decodedText);
                    if (!matched) {
                        if (statusEl) {
                            statusEl.innerHTML = '<span class="text-warning fw-bold">Scanned: ' + decodedText + ' (Customer not found)</span>';
                        }
                    }
                },
                function(error) {
                    // Constant decoding frame noise, ignore
                }
            ).then(() => {
                isScanningActive = true;
                if (spinner) spinner.style.display = 'none';
                if (statusEl) statusEl.textContent = 'Align customer barcode within frame';
            }).catch(err => {
                console.error("Camera start error:", err);
                if (spinner) spinner.style.display = 'none';
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-danger small"><i class="bi bi-camera-video-off me-1"></i> Camera inaccessible (' + (err.message || 'permission denied') + '). Enter code below.</span>';
                }
            });
        }

        function stopCameraScanner() {
            isScanningActive = false;
            if (html5QrCodeScanner) {
                html5QrCodeScanner.stop().catch(err => {
                    // Ignore already stopped
                }).finally(() => {
                    const spinner = document.getElementById('scanner-loading-spinner');
                    if (spinner) spinner.style.display = 'none';
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('customerBarcodeScannerModal');
            if (!modalEl) return;

            // Listen to all buttons with .btn-customer-scanner
            document.querySelectorAll('.btn-customer-scanner').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    activeSelectTargetId = this.getAttribute('data-target') || 'customer_id';
                    
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                });
            });

            // Start camera when modal finishes opening
            modalEl.addEventListener('shown.bs.modal', function() {
                startCameraScanner();
                const manualInput = document.getElementById('manual-customer-barcode-input');
                if (manualInput) {
                    manualInput.value = '';
                    manualInput.focus();
                }
            });

            // Cleanly stop camera when modal closes
            modalEl.addEventListener('hidden.bs.modal', function() {
                stopCameraScanner();
            });

            // Manual fallback input handling
            const applyBtn = document.getElementById('btn-manual-customer-apply');
            const manualInput = document.getElementById('manual-customer-barcode-input');

            if (applyBtn && manualInput) {
                function handleManualApply() {
                    const val = manualInput.value.trim();
                    if (val) {
                        const matched = matchAndSelectCustomer(val);
                        if (!matched) {
                            const statusEl = document.getElementById('scanner-status');
                            if (statusEl) statusEl.innerHTML = '<span class="text-danger fw-bold">Customer code "' + val + '" not found.</span>';
                        }
                    }
                }

                applyBtn.addEventListener('click', handleManualApply);
                manualInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleManualApply();
                    }
                });
            }
        });
    })();
</script>
