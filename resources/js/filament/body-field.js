document.addEventListener('alpine:init', () => {
    Alpine.store('bodyImageInsert', {
        open: false,
        src: '',
        title: '',
        position: 'left',
    });

    Alpine.store('bodyImages', {
        items: [],
        docked: false,

        seed(serverItems) {
            this.items = serverItems.map((item) => ({
                key: item.key,
                src: item.thumbnail,
                insertSrc: item.insertSrc,
                title: item.label,
                isProcessing: false,
                pending: item.pending,
            }));
        },

        addUpload(previewUrl, title, insertSrc) {
            this.items.push({
                key: 'upload-' + Date.now(),
                src: previewUrl,
                insertSrc,
                title,
                isProcessing: true,
                pending: true,
            });
        },

        uploadFinished() {
            const item = this.items.find((i) => i.isProcessing);

            if (item) {
                item.isProcessing = false;
            }
        },

        remove(title) {
            const index = this.items.findIndex((i) => i.title === title);

            if (index !== -1) {
                const item = this.items[index];

                if (item.src?.startsWith('blob:')) {
                    URL.revokeObjectURL(item.src);
                }

                this.items.splice(index, 1);
            }
        },
    });

    Alpine.data('bodyField', ({
        state,
        shouldAutosize,
        initialHeight,
        headingModalId,
        buttonModalId,
        iframeModalId,
        imagesModalId,
        previewButtonUrl,
    }) => ({
        state,
        wrapperEl: null,
        customError: null,
        heading: '',
        buttonTheme: 'primary',
        buttonSize: 'md',
        buttonLabel: '',
        buttonHref: '',
        buttonNewWindow: false,
        buttonBoldText: false,
        buttonWrapperStyles: '',
        iframeUrl: '',
        cursorPosition: 0,

        init() {
            this.$watch('state', this.debounce((value) => this.validateHtml(value), 300));
            this.validateHtml(this.state);
            this.initAutosize();

            window.addEventListener('FilePond:addfilestart', (event) => {
                const file = event.detail?.file?.file;
                if (!file) return;
                const previewUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
                const insertSrc = file.name.replace(/[#/\\ ]/g, '-');
                Alpine.store('bodyImages').addUpload(previewUrl, file.name, insertSrc);
            });
            window.addEventListener('upload:finished', () => Alpine.store('bodyImages').uploadFinished());
            window.addEventListener('FilePond:removefile', (event) => {
                const title = event.detail?.file?.filename ?? event.detail?.file?.file?.name;
                if (title) Alpine.store('bodyImages').remove(title);
            });
            window.addEventListener('FilePond:error', (event) => {
                const title = event.detail?.file?.filename ?? event.detail?.file?.file?.name;
                if (title) Alpine.store('bodyImages').remove(title);
            });
            window.addEventListener('body-image-open-insert', (event) => {
                Alpine.store('bodyImageInsert').src = event.detail.src;
                Alpine.store('bodyImageInsert').title = event.detail.title || '';
                Alpine.store('bodyImageInsert').position = 'left';
                Alpine.store('bodyImageInsert').open = true;
            });
            window.addEventListener('body-image-do-insert', () => {
                const { src, title, position } = Alpine.store('bodyImageInsert');
                this.insertAtCursor(`<article-image src="${src}" title="${title}" position="${position}"></article-image>`);
                Alpine.store('bodyImageInsert').open = false;
                Alpine.store('bodyImageInsert').src = '';
                Alpine.store('bodyImageInsert').title = '';
                Alpine.store('bodyImageInsert').position = 'left';
                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: imagesModalId } }));
            });
        },

        recordCursorPosition() {
            this.cursorPosition = this.$refs.textarea.selectionStart;
        },

        insertAtCursor(text) {
            const textarea = this.$refs.textarea;
            const cursorPosition = this.cursorPosition;

            textarea.focus();
            textarea.setSelectionRange(cursorPosition, cursorPosition);

            // `insertText` fires a real `input` event at the cursor position,
            // so the textarea's `x-model`/`wire:model` bindings pick up the
            // change exactly as if the user had typed it - keeping the cursor
            // in place instead of jumping to the end.
            if (document.queryCommandSupported && document.queryCommandSupported('insertText')) {
                document.execCommand('insertText', false, text);

                return;
            }

            const before = this.state.substring(0, cursorPosition);
            const after = this.state.substring(cursorPosition);
            const newValue = before + text + after;

            textarea.value = newValue;
            textarea.selectionStart = textarea.selectionEnd = cursorPosition + text.length;

            this.state = newValue;
        },

        addHeader() {
            this.insertAtCursor(`<article-header>${this.heading}</article-header>`);
            this.heading = '';
            this.$dispatch('close-modal', { id: headingModalId });
        },

        addIframe() {
            this.insertAtCursor(`<article-iframe src='${this.iframeUrl}'></article-iframe>`);
            this.iframeUrl = '';
            this.$dispatch('close-modal', { id: iframeModalId });
        },

        addButton() {
            const attrs = [
                `theme='${this.buttonTheme}'`,
                `size='${this.buttonSize}'`,
                `href='${this.buttonHref}'`,
                this.buttonNewWindow ? `target='_blank'` : '',
                `wrapper-styles='${this.buttonWrapperStyles}'`,
                this.buttonBoldText ? 'bold' : '',
            ].filter(Boolean).join(' ');

            this.insertAtCursor(`<article-button ${attrs}>${this.buttonLabel}</article-button>`);

            this.buttonTheme = 'primary';
            this.buttonSize = 'md';
            this.buttonLabel = '';
            this.buttonHref = '';
            this.buttonNewWindow = false;
            this.buttonBoldText = false;
            this.buttonWrapperStyles = '';

            this.$dispatch('close-modal', { id: buttonModalId });
        },


        buttonPreviewUrl() {
            const url = new URL(previewButtonUrl);

            url.searchParams.set('size', this.buttonSize);
            url.searchParams.set('theme', this.buttonTheme);
            url.searchParams.set('label', this.buttonLabel);
            url.searchParams.set('href', this.buttonHref);
            url.searchParams.set('wrapperStyles', this.buttonWrapperStyles);

            if (this.buttonBoldText) {
                url.searchParams.set('bold', '1');
            }

            return url.toString();
        },

        debounce(fn, wait) {
            let timeout;

            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), wait);
            };
        },

        validateHtml(content) {
            const doc = new DOMParser().parseFromString(`<div>${content ?? ''}</div>`, 'text/html');

            if (doc.querySelector('parsererror')) {
                this.customError = 'Invalid HTML detected. Please check for broken or mismatched tags.';

                return;
            }

            const mismatchedCaseTags = /<([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>([^<>]*)<\/([a-zA-Z][a-zA-Z0-9]*)>/g;

            let match;

            while ((match = mismatchedCaseTags.exec(content ?? '')) !== null) {
                const [, open, , close] = match;

                if (open !== close) {
                    this.customError = `Mismatched tag casing detected: <${open}>...</${close}>. Tag names must match exactly.`;

                    return;
                }
            }

            this.customError = null;
        },

        initAutosize() {
            this.wrapperEl = this.$refs.textarea.parentNode;
            this.setInitialHeight();

            if (shouldAutosize) {
                this.$watch('state', () => this.resize());
            } else {
                this.setUpResizeObserver();
            }
        },

        setInitialHeight() {
            if (this.$refs.textarea.scrollHeight <= 0) {
                return;
            }

            this.wrapperEl.style.height = initialHeight + 'rem';
        },

        resize() {
            const textarea = this.$refs.textarea;

            if (textarea.scrollHeight <= 0) {
                return;
            }

            const previousHeight = textarea.style.height;
            textarea.style.height = '0px';

            const contentHeight = textarea.scrollHeight;
            textarea.style.height = previousHeight;

            const minHeightPx = initialHeight * parseFloat(getComputedStyle(document.documentElement).fontSize);
            const newHeight = Math.max(contentHeight, minHeightPx) + 'px';

            if (this.wrapperEl.style.height === newHeight) {
                return;
            }

            this.wrapperEl.style.height = newHeight;
        },

        setUpResizeObserver() {
            const observer = new ResizeObserver(() => {
                this.wrapperEl.style.height = this.$refs.textarea.style.height;
            });

            observer.observe(this.$refs.textarea);
        },
    }));
});
