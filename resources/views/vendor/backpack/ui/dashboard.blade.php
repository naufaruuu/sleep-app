@extends(backpack_view('blank'))

@push('after_styles')
    <style>
        /* Base styles for both light/dark modes */
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border: none;
            transition: all 0.3s ease;
        }

        .dashboard-card .card-body {
            padding: 2rem;
        }

        .markdown-body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            box-sizing: border-box;
            min-width: 200px;
            max-width: 980px;
            margin: 0 auto;
            padding: 0;
            font-size: 16px;
            line-height: 1.6;
        }

        /* Enhanced Typography */
        .markdown-body h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .markdown-body h2 {
            font-size: 1.75rem;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            font-weight: 600;
        }

        .markdown-body h3 {
            font-size: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .markdown-body p {
            font-size: 1rem;
            margin-bottom: 1.25rem;
            line-height: 1.7;
        }

        .markdown-body ol {
            padding-left: 1.5rem;
        }

        .markdown-body ol li {
            margin-bottom: 1rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .markdown-body li p {
            margin-top: 0.5rem;
        }

        /* Light mode styles */
        html:not([data-bs-theme="dark"]) .dashboard-card,
        body:not(.dark) .dashboard-card {
            background-color: #fff;
        }

        html:not([data-bs-theme="dark"]) .markdown-body,
        body:not(.dark) .markdown-body {
            color: #24292e;
        }

        html:not([data-bs-theme="dark"]) .markdown-body h1,
        html:not([data-bs-theme="dark"]) .markdown-body h2,
        html:not([data-bs-theme="dark"]) .markdown-body h3,
        html:not([data-bs-theme="dark"]) .markdown-body h4,
        body:not(.dark) .markdown-body h1,
        body:not(.dark) .markdown-body h2,
        body:not(.dark) .markdown-body h3,
        body:not(.dark) .markdown-body h4 {
            color: #1f2937;
        }

        html:not([data-bs-theme="dark"]) .markdown-body h2,
        body:not(.dark) .markdown-body h2 {
            border-bottom: 1px solid #eaecef;
        }

        /* Code block enhancement */
        html:not([data-bs-theme="dark"]) .markdown-body pre,
        body:not(.dark) .markdown-body pre {
            background-color: #f7f7f7 !important;
            border: 1px solid #e1e4e8;
        }

        html:not([data-bs-theme="dark"]) .markdown-body code,
        html:not([data-bs-theme="dark"]) .dark-bg,
        body:not(.dark) .markdown-body code,
        body:not(.dark) .dark-bg {
            background-color: #f6f8fa !important;
            color: #24292e !important;
            font-size: 0.9rem;
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
        }

        /* Dark mode styles */
        html[data-bs-theme="dark"] .dashboard-card,
        body.dark .dashboard-card {
            background-color: #1e2030;
            border: 1px solid #2c2e40;
        }

        html[data-bs-theme="dark"] .markdown-body,
        body.dark .markdown-body {
            color: #e2e8f0;
            background-color: transparent;
        }

        html[data-bs-theme="dark"] .markdown-body h1,
        html[data-bs-theme="dark"] .markdown-body h2,
        html[data-bs-theme="dark"] .markdown-body h3,
        html[data-bs-theme="dark"] .markdown-body h4,
        html[data-bs-theme="dark"] .markdown-body h5,
        html[data-bs-theme="dark"] .markdown-body h6,
        body.dark .markdown-body h1,
        body.dark .markdown-body h2,
        body.dark .markdown-body h3,
        body.dark .markdown-body h4,
        body.dark .markdown-body h5,
        body.dark .markdown-body h6 {
            color: #f8fafc;
        }

        html[data-bs-theme="dark"] .markdown-body h2,
        body.dark .markdown-body h2 {
            border-bottom: 1px solid #2d3748;
        }

        /* Code block enhancement for dark mode */
        html[data-bs-theme="dark"] .markdown-body pre,
        body.dark .markdown-body pre {
            background-color: #111827 !important;
            border: 1px solid #374151;
            padding: 1.25rem;
            margin: 1.5rem 0;
        }

        html[data-bs-theme="dark"] .markdown-body code,
        html[data-bs-theme="dark"] .dark-bg,
        body.dark .markdown-body code,
        body.dark .dark-bg {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
            font-size: 0.9rem;
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
        }

        /* Inline code enhancement */
        .markdown-body :not(pre)>code {
            padding: 0.2em 0.4em;
            border-radius: 3px;
        }

        /* Additional elements */
        html[data-bs-theme="dark"] .markdown-body blockquote,
        body.dark .markdown-body blockquote {
            color: #9ca3af;
            border-left: 3px solid #4b5563;
            padding-left: 1rem;
            background-color: rgba(71, 85, 105, 0.1);
        }

        html:not([data-bs-theme="dark"]) .markdown-body blockquote,
        body:not(.dark) .markdown-body blockquote {
            color: #6b7280;
            border-left: 3px solid #d1d5db;
            padding-left: 1rem;
            background-color: rgba(243, 244, 246, 0.5);
        }

        /* Code sample section enhancement */
        .markdown-body pre {
            border-radius: 8px;
            margin: 1.5rem 0;
            padding: 1.25rem;
            overflow: auto;
        }

        .code-sample-block {
            background-color: #1a202c;
            color: #e2e8f0;
            border-radius: 8px;
            margin: 1.5rem 0;
            overflow: hidden;
        }

        html:not([data-bs-theme="dark"]) .code-sample-block,
        body:not(.dark) .code-sample-block {
            background-color: #f7fafc;
            color: #1a202c;
            border: 1px solid #e2e8f0;
        }

        html[data-bs-theme="dark"] .code-sample-block,
        body.dark .code-sample-block {
            background-color: #1a202c;
            color: #e2e8f0;
            border: 1px solid #2d3748;
        }

        /* Loading spinner */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(99, 102, 241, 0.2);
            border-radius: 50%;
            border-top-color: rgba(99, 102, 241, 0.8);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@push('after_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/4.3.0/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contentDiv = document.getElementById('markdown-content');
            const loadingDiv = document.getElementById('loading');

            // Set up custom renderer for marked
            const renderer = new marked.Renderer();

            // Custom code block renderer with enhanced styling
            renderer.code = function(code, language) {
                return `
                <div class="code-sample-block">
                    <pre><code class="dark-bg ${language || ''}">${code}</code></pre>
                </div>`;
            };

            // Function to fetch and render markdown content
            async function loadMarkdownContent() {
                try {
                    const response = await fetch('/storage/readme.md');

                    if (!response.ok) {
                        throw new Error(`Failed to load README.md (${response.status})`);
                    }

                    const markdownText = await response.text();

                    // Set renderer options
                    marked.setOptions({
                        renderer: renderer,
                        gfm: true,
                        breaks: true,
                        highlight: function(code) {
                            return code;
                        }
                    });

                    // Parse markdown to HTML
                    contentDiv.innerHTML = marked.parse(markdownText);

                    // Wrap code blocks for better styling
                    document.querySelectorAll('.markdown-body pre').forEach(preElement => {
                        if (!preElement.parentElement.classList.contains('code-sample-block')) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'code-sample-block';
                            preElement.parentNode.insertBefore(wrapper, preElement);
                            wrapper.appendChild(preElement);
                        }
                    });

                    // Hide loading spinner
                    loadingDiv.style.display = 'none';
                    contentDiv.style.display = 'block';

                } catch (error) {
                    console.error('Error loading markdown content:', error);
                    const alertClass = isDarkMode() ? 'alert-secondary' : 'alert-danger';
                    contentDiv.innerHTML = `
                    <div class="alert ${alertClass}">
                        <h4>Error Loading Content</h4>
                        <p>${error.message}</p>
                        <p>Please make sure the README.md file exists at the expected location.</p>
                    </div>
                `;
                    loadingDiv.style.display = 'none';
                    contentDiv.style.display = 'block';
                }
            }

            // Helper function to check dark mode
            function isDarkMode() {
                return document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
                    document.body.classList.contains('dark');
            }

            // Load content when page loads
            loadMarkdownContent();
        });
    </script>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div id="loading" class="loading">
                            <div class="loading-spinner"></div>
                        </div>
                        <div id="markdown-content" class="markdown-body" style="display: none;">
                            <!-- Markdown content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
