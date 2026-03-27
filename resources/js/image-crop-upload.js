import Cropper from 'cropperjs';

const cropperTemplate = `
    <cropper-canvas>
        <cropper-image initial-center-size="cover" translatable scalable></cropper-image>
        <cropper-shade theme-color="rgba(24, 24, 27, 0.45)"></cropper-shade>
        <cropper-handle action="move" plain></cropper-handle>
        <cropper-selection initial-coverage="0.62" aspect-ratio="1" movable precise>
            <cropper-handle action="move" plain></cropper-handle>
        </cropper-selection>
    </cropper-canvas>
`;

window.imageCropUpload = function imageCropUpload({
    $wire,
    modalModel,
    previewModel,
    outputModel,
    size = 600,
}) {
    return {
        cropper: null,
        imageUrl: '',
        isSaving: false,

        init() {
            this.imageUrl = this.$wire[previewModel] ?? '';

            this.$watch(() => this.$wire[modalModel], (visible) => {
                if (visible) {
                    this.imageUrl = this.$wire[previewModel] ?? '';
                    this.$nextTick(() => {
                        this.mountCropper();
                    });

                    return;
                }

                this.destroyCropper();
            });

            this.$watch(() => this.$wire[previewModel], (value) => {
                this.imageUrl = value ?? '';

                if (this.$wire[modalModel]) {
                    this.$nextTick(() => {
                        this.mountCropper(true);
                    });
                }
            });
        },

        mountCropper(force = false) {
            if (!this.imageUrl || !this.$refs.image) {
                return;
            }

            if (this.cropper && !force) {
                return;
            }

            this.destroyCropper();

            const image = this.$refs.image;

            const initializeCropper = () => {
                if (this.cropper) {
                    return;
                }

                this.cropper = new Cropper(image, {
                    container: this.$refs.cropperHost,
                    template: cropperTemplate,
                });

                const cropperCanvas = this.cropper.getCropperCanvas();
                const selection = this.cropper.getCropperSelection();
                const cropperImage = this.cropper.getCropperImage();

                if (cropperCanvas) {
                    cropperCanvas.style.width = '100%';
                    cropperCanvas.style.height = '100%';
                }

                if (cropperImage) {
                    cropperImage.$ready(() => {
                        cropperImage.$center('cover');
                    });
                }

                if (selection) {
                    selection.aspectRatio = 1;
                    selection.initialAspectRatio = 1;
                    selection.initialCoverage = 0.62;
                    selection.movable = true;
                    selection.resizable = false;
                    selection.zoomable = false;
                    selection.outlined = false;
                    selection.precise = true;
                    selection.active = true;
                    selection.$center();
                }
            };

            image.onload = () => {
                initializeCropper();
            };

            if (image.src !== this.imageUrl) {
                image.src = this.imageUrl;
            }

            if (image.complete && image.naturalWidth > 0) {
                initializeCropper();
            }
        },

        zoomIn() {
            const cropperImage = this.cropper?.getCropperImage();

            if (cropperImage) {
                cropperImage.$zoom(0.1);
            }
        },

        zoomOut() {
            const cropperImage = this.cropper?.getCropperImage();

            if (cropperImage) {
                cropperImage.$zoom(-0.1);
            }
        },

        destroyCropper() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },

        async saveCrop() {
            if (!this.cropper) {
                return;
            }

            const selection = this.cropper.getCropperSelection();

            if (!selection) {
                return;
            }

            this.isSaving = true;

            try {
                const canvas = await selection.$toCanvas({
                    width: size,
                    height: size,
                });

                const dataUrl = canvas.toDataURL('image/png');

                this.$wire[outputModel] = dataUrl;
                await this.$wire.confirmAvatarCrop();
            } finally {
                this.isSaving = false;
            }
        },
    };
};
