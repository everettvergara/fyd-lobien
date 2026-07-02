import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/autoresize';
import { EditorView, basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { EditorState } from '@codemirror/state';

const editorInstances = new Map();

const syncTextareaFromEditor = (field) => {
    const textarea = field.querySelector('[data-rich-text-target]');
    const view = editorInstances.get(field)?.codemirror;

    if (view) {
        textarea.value = view.state.doc.toString();
    } else {
        const editor = tinymce.get(textarea.id);
        if (editor) {
            textarea.value = editor.getContent();
        }
    }
};

const setMode = (field, mode) => {
    const visualWrap = field.querySelector('[data-rich-text-visual]');
    const sourceWrap = field.querySelector('[data-rich-text-source]');
    const textarea = field.querySelector('[data-rich-text-target]');
    const instance = editorInstances.get(field) ?? {};
    const isVisual = mode === 'visual';

    field.querySelectorAll('[data-rich-text-mode]').forEach((button) => {
        button.classList.toggle('active', button.dataset.richTextMode === mode);
    });

    if (isVisual) {
        const content = instance.codemirror?.state.doc.toString() ?? textarea.value;
        textarea.value = content;

        if (instance.codemirror) {
            instance.codemirror.destroy();
            instance.codemirror = null;
        }

        visualWrap.classList.remove('d-none');
        sourceWrap.classList.add('d-none');

        const editor = tinymce.get(textarea.id);
        if (editor) {
            editor.setContent(content);
            editor.show();
        } else {
            initTinyMce(field, textarea);
        }
    } else {
        const editor = tinymce.get(textarea.id);
        const content = editor ? editor.getContent() : textarea.value;

        if (editor) {
            editor.hide();
        }

        textarea.value = content;
        visualWrap.classList.add('d-none');
        sourceWrap.classList.remove('d-none');

        const mount = field.querySelector('[data-rich-text-codemirror]');
        mount.innerHTML = '';

        const view = new EditorView({
            parent: mount,
            state: EditorState.create({
                doc: content,
                extensions: [
                    basicSetup,
                    html(),
                    EditorView.lineWrapping,
                    EditorView.updateListener.of((update) => {
                        if (update.docChanged) {
                            textarea.value = update.state.doc.toString();
                        }
                    }),
                ],
            }),
        });

        instance.codemirror = view;
        editorInstances.set(field, instance);
    }
};

const initTinyMce = (field, textarea) => {
    const compact = field.hasAttribute('data-rich-text-compact');

    tinymce.init({
        target: textarea,
        skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@7/skins/ui/oxide',
        content_css: 'https://cdn.jsdelivr.net/npm/tinymce@7/skins/content/default/content.min.css',
        menubar: false,
        statusbar: false,
        height: compact ? 220 : 360,
        plugins: 'lists link autoresize',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | image',
        toolbar_mode: 'wrap',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
        content_style: 'body { font-family: system-ui, sans-serif; font-size: 0.6875rem; }',
        promotion: false,
        branding: false,
        license_key: 'gpl',
        setup: (editor) => {
            editor.ui.registry.addButton('image', {
                icon: 'image',
                tooltip: 'Insert image(s)',
                onAction: () => {
                    window.openMediaPicker?.({
                        mode: 'multi',
                        onSelect: (items) => {
                            items.forEach((item) => {
                                const alt = item.alt ? item.alt.replace(/"/g, '&quot;') : '';
                                editor.insertContent(`<p><img src="${item.url}" alt="${alt}"></p>`);
                            });
                        },
                    });
                },
            });
        },
    });
};

const initRichTextEditors = () => {
    document.querySelectorAll('[data-rich-text]').forEach((field) => {
        const textarea = field.querySelector('[data-rich-text-target]');
        if (! textarea?.id || editorInstances.has(field)) {
            return;
        }

        editorInstances.set(field, {});

        field.querySelectorAll('[data-rich-text-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                setMode(field, button.dataset.richTextMode);
            });
        });

        initTinyMce(field, textarea);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initRichTextEditors();

    document.querySelectorAll('form').forEach((form) => {
        if (! form.querySelector('[data-rich-text-target]')) {
            return;
        }

        form.addEventListener('submit', () => {
            document.querySelectorAll('[data-rich-text]').forEach((field) => {
                syncTextareaFromEditor(field);
            });
            tinymce.triggerSave();
        });
    });
});

export { initRichTextEditors };
