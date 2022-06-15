<html>
    <head>
        <title>{{ code }} Error</title>
        <link href="/bundle/index.css" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div class="content">
            <div class="error-modal">
                <div class="error-modal-menu">
                    <button data-entity="home">Home</button>
                    <button data-entity="expand">Expand</button>
                </div>
                
                <div class="error-modal-code error-row">{{ code }}</div>
                <div class="error-modal-message error-row">{{ message }}</div>
                
                {% if extMessage and extMessage != '' %}
                    <div class="error-modal-additional error-row">
                        <pre>{{ extMessage }}</pre>
                    </div>
                {% endif %}
            </div>
        </div>
        
        <script type="text/javascript">
            window.error = {
                code: {{ code }},
                message: `{{ message|e('js') }}`,
                extended: `{{ extMessage|e('js') }}`
            }
        </script>

        <script src="/bundle/index.js"></script>

    </body>
</html>
