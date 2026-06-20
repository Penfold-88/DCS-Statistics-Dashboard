(function () {
    const wrapper = document.querySelector('[data-cms-editor]');
    const form = document.getElementById('cms-page-form');
    if (!wrapper || !form) return;

    const editor = wrapper.querySelector('.cms-editor-content');
    const textarea = document.getElementById('cms_content');
    const format = wrapper.querySelector('[data-editor-format]');
    const text = window.DCS_CMS_EDITOR_TEXT || {};

    function focusEditor() {
        editor.focus();
    }

    function run(command, value) {
        focusEditor();
        document.execCommand(command, false, value || null);
        sync();
    }

    function sync() {
        textarea.value = editor.innerHTML.trim();
    }

    wrapper.querySelectorAll('[data-editor-command]').forEach(button => {
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => run(button.dataset.editorCommand));
    });

    format.addEventListener('change', () => {
        run('formatBlock', format.value);
        format.value = 'p';
    });

    const linkButton = wrapper.querySelector('[data-editor-link]');
    linkButton.addEventListener('mousedown', event => event.preventDefault());
    linkButton.addEventListener('click', () => {
        const selection = window.getSelection();
        if (!selection || selection.isCollapsed) {
            focusEditor();
            return;
        }
        const href = window.prompt(text.linkPrompt || 'Enter a link URL (https://...)');
        if (href) run('createLink', href.trim());
    });

    editor.addEventListener('input', sync);
    editor.addEventListener('paste', event => {
        event.preventDefault();
        const plainText = (event.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, plainText);
    });
    form.addEventListener('submit', event => {
        sync();
        if (!editor.textContent.trim()) {
            event.preventDefault();
            window.alert(text.contentRequired || 'Page content is required.');
            focusEditor();
        }
    });
    sync();
}());
