import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import mjml from 'vite-plugin-mjml'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        mjml({
			input: 'resources/mail',
			output: 'resources/views/emails',
			extension: '.blade.php',
		}),
    ],
});
