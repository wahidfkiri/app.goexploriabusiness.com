<script>
(function () {
    const form = document.getElementById('blogForm');
    if (!form) return;

    const contentField = document.getElementById('blogContentEditor');
    const saveAndPublishBtn = document.getElementById('saveAndPublishBtn');
    const publishNowBtn = document.getElementById('publishNowBtn');
    const statusField = document.getElementById('statusField');
    const publishedAtField = form.querySelector('[name="published_at"]');

    const aiGenerateEndpoint = document.getElementById('aiGenerateEndpoint');
    const aiBtn = document.getElementById('generateWithAiBtn');
    const aiStatus = document.getElementById('aiGenerationStatus');
    const aiSubject = document.getElementById('aiSubject');
    const aiKeyword = document.getElementById('aiKeyword');
    const aiBusinessContext = document.getElementById('aiBusinessContext');
    const aiTone = document.getElementById('aiTone');
    const aiMinWords = document.getElementById('aiMinWords');

    let blogEditor = null;

    function syncEditorToTextarea() {
        if (blogEditor && contentField) {
            contentField.value = blogEditor.getData();
        }
    }

    function loadCkEditorScript() {
        return new Promise((resolve, reject) => {
            if (typeof ClassicEditor !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js';
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Impossible de charger CKEditor'));
            document.head.appendChild(script);
        });
    }

    async function initEditor() {
        if (!contentField) return;

        try {
            await loadCkEditorScript();
            if (typeof ClassicEditor === 'undefined') return;

            blogEditor = await ClassicEditor.create(contentField, {
                toolbar: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    '|',
                    'blockQuote',
                    'insertTable',
                    '|',
                    'undo',
                    'redo'
                ],
                language: 'fr'
            });
        } catch (error) {
            console.error(error);
        }
    }

    async function submitBlogForm() {
        syncEditorToTextarea();

        const action = form.getAttribute('action');
        const formData = new FormData(form);

        try {
            const response = await fetch(action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                alert(data.message || 'Erreur de sauvegarde');
                return;
            }

            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            window.location.reload();
        } catch (error) {
            alert('Erreur de sauvegarde');
        }
    }

    function publishImmediately() {
        statusField.value = 'published';

        if (publishedAtField) {
            publishedAtField.value = '';
        }
    }

    function setFieldValue(selector, value) {
        const el = document.querySelector(selector);
        if (!el || typeof value !== 'string' || value.trim() === '') return;
        el.value = value;
    }

    async function generateWithAi() {
        if (!aiGenerateEndpoint || !aiBtn) return;

        const subject = (aiSubject?.value || '').trim();
        if (!subject) {
            alert('Veuillez renseigner le sujet avant la génération IA.');
            return;
        }

        aiBtn.disabled = true;
        if (aiStatus) aiStatus.textContent = 'Generation IA en cours...';

        try {
            const response = await fetch(aiGenerateEndpoint.value, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    subject: subject,
                    target_keyword: (aiKeyword?.value || '').trim(),
                    business_context: (aiBusinessContext?.value || '').trim(),
                    tone: aiTone?.value || 'professionnel',
                    language: 'fr',
                    min_words: Number(aiMinWords?.value || 900)
                })
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Echec de generation IA');
            }

            const data = result.data || {};
            setFieldValue('#blogTitleField', data.title || '');
            setFieldValue('#blogSlugField', data.slug || '');
            setFieldValue('#blogExcerptField', data.excerpt || '');
            setFieldValue('#blogSeoTitleField', data.seo_title || '');
            setFieldValue('#blogSeoDescriptionField', data.seo_description || '');
            setFieldValue('#blogSeoKeywordsField', data.seo_keywords || '');
            setFieldValue('#blogTagsField', data.tags || '');
            setFieldValue('#blogCanonicalUrlField', data.canonical_url || '');

            if (statusField) {
                statusField.value = 'draft';
            }

            if (typeof data.content === 'string' && data.content.trim() !== '') {
                if (blogEditor) {
                    blogEditor.setData(data.content);
                } else if (contentField) {
                    contentField.value = data.content;
                }
            }

            if (aiStatus) aiStatus.textContent = 'Generation terminee. Pensez a relire avant publication.';
        } catch (error) {
            if (aiStatus) aiStatus.textContent = '';
            alert(error.message || 'Erreur generation IA');
        } finally {
            aiBtn.disabled = false;
        }
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        await submitBlogForm();
    });

    if (saveAndPublishBtn && statusField) {
        saveAndPublishBtn.addEventListener('click', function () {
            publishImmediately();
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        });
    }

    if (publishNowBtn && statusField) {
        publishNowBtn.addEventListener('click', function () {
            publishImmediately();
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        });
    }

    if (aiBtn) {
        aiBtn.addEventListener('click', generateWithAi);
    }

    initEditor();
})();
</script>
