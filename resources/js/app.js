import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.data('documentationLightbox', (images = []) => ({
    images,

    preview: false,
    previewIndex: 0,

    zoom: 1,
    minZoom: 1,
    maxZoom: 3,

    translateX: 0,
    translateY: 0,

    isDragging: false,

    dragStartX: 0,
    dragStartY: 0,

    startTranslateX: 0,
    startTranslateY: 0,

    pointers: {},

    pinchStartDistance: 0,
    pinchStartZoom: 1,

    openPreview(index) {
        if (
            !this.images.length ||
            !Number.isInteger(index) ||
            index < 0 ||
            index >= this.images.length
        ) {
            return;
        }

        this.previewIndex = index;
        this.resetZoom();
        this.preview = true;

        document.body.classList.add('overflow-hidden');
    },

    closePreview() {
        this.preview = false;
        this.resetZoom();

        document.body.classList.remove('overflow-hidden');
    },

    nextPreview() {
        if (!this.images.length) return;

        if (this.previewIndex < this.images.length - 1) {
            this.previewIndex++;
        } else {
            this.previewIndex = 0;
        }

        this.resetZoom();
    },

    previousPreview() {
        if (!this.images.length) return;

        if (this.previewIndex > 0) {
            this.previewIndex--;
        } else {
            this.previewIndex = this.images.length - 1;
        }

        this.resetZoom();
    },

    resetZoom() {
        this.zoom = 1;
        this.translateX = 0;
        this.translateY = 0;

        this.isDragging = false;
        this.pointers = {};
    },

    zoomIn() {
        this.setZoom(this.zoom + 0.5);
    },

    zoomOut() {
        this.setZoom(this.zoom - 0.5);
    },

    toggleZoom() {
        if (this.zoom > 1) {
            this.resetZoom();
        } else {
            this.setZoom(2);
        }
    },

    setZoom(value) {
        this.zoom = Math.min(
            this.maxZoom,
            Math.max(this.minZoom, value)
        );

        if (this.zoom === 1) {
            this.translateX = 0;
            this.translateY = 0;
        } else {
            this.clampPosition();
        }
    },

    handleWheel(event) {
        if (!this.preview) return;

        const direction = event.deltaY < 0 ? 0.25 : -0.25;

        this.setZoom(this.zoom + direction);
    },

    handlePointerDown(event) {
        if (!this.preview) return;

        this.pointers[event.pointerId] = {
            x: event.clientX,
            y: event.clientY,
        };

        event.currentTarget.setPointerCapture(event.pointerId);

        const pointerIds = Object.keys(this.pointers);

        if (pointerIds.length === 2) {
            const first = this.pointers[pointerIds[0]];
            const second = this.pointers[pointerIds[1]];

            this.pinchStartDistance = Math.hypot(
                second.x - first.x,
                second.y - first.y
            );

            this.pinchStartZoom = this.zoom;
            this.isDragging = false;

            return;
        }

        if (this.zoom > 1) {
            this.isDragging = true;

            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;

            this.startTranslateX = this.translateX;
            this.startTranslateY = this.translateY;
        }
    },

    handlePointerMove(event) {
        if (
            !this.preview ||
            !this.pointers[event.pointerId]
        ) {
            return;
        }

        this.pointers[event.pointerId].x = event.clientX;
        this.pointers[event.pointerId].y = event.clientY;

        const pointerIds = Object.keys(this.pointers);

        if (pointerIds.length === 2) {
            const first = this.pointers[pointerIds[0]];
            const second = this.pointers[pointerIds[1]];

            const distance = Math.hypot(
                second.x - first.x,
                second.y - first.y
            );

            if (this.pinchStartDistance > 0) {
                const scale =
                    distance / this.pinchStartDistance;

                this.setZoom(
                    this.pinchStartZoom * scale
                );
            }

            return;
        }

        if (!this.isDragging || this.zoom <= 1) {
            return;
        }

        const deltaX =
            event.clientX - this.dragStartX;

        const deltaY =
            event.clientY - this.dragStartY;

        this.translateX =
            this.startTranslateX + deltaX;

        this.translateY =
            this.startTranslateY + deltaY;

        this.clampPosition();
    },

    handlePointerUp(event) {
        delete this.pointers[event.pointerId];

        const pointerIds = Object.keys(this.pointers);

        if (pointerIds.length < 2) {
            this.pinchStartDistance = 0;
        }

        if (pointerIds.length === 0) {
            this.isDragging = false;
        }
    },

    clampPosition() {
        if (this.zoom <= 1) {
            this.translateX = 0;
            this.translateY = 0;

            return;
        }

        const image = this.$refs.previewImage;

        if (!image) return;

        const maxX =
            (image.clientWidth * (this.zoom - 1)) / 2;

        const maxY =
            (image.clientHeight * (this.zoom - 1)) / 2;

        this.translateX = Math.min(
            maxX,
            Math.max(-maxX, this.translateX)
        );

        this.translateY = Math.min(
            maxY,
            Math.max(-maxY, this.translateY)
        );
    },
}));
Alpine.start();