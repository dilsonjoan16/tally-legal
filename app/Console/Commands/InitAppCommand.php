<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the application by migrating the database, running seeders, and creating an admin user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Iniciando la configuración de la aplicación...');
            $this->newLine();

            // Migrate the database
            $this->simulateStep('Migrando base de datos', function () {
                Artisan::call('migrate', ['--force' => true]);
            }, 'Base de datos migrada con éxito.');

            $this->newLine();

            // Run the Seeders
            $this->simulateStep('Poblando base de datos', function () {
                Artisan::call('db:seed', ['--force' => true]);
            }, 'Base de datos poblada con éxito.');

            $this->newLine();

            // Create administrator user
            $this->simulateStep('Creando usuario administrador', function () {
                $this->createAdminUser();
            }, 'Usuario administrador creado con éxito.');

            $this->newLine();

            $this->info('La aplicación ha sido configurada correctamente.');

            $this->newLine();

            // Execute tails processes.
            $this->warn('Corriendo procesos en cola... Puede utilizar la aplicacion sin problema alguno, no se recomienda cerrar la terminal.');
            Artisan::call('queue:work');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());

            Artisan::call('db:wipe');
        }
    }

    /**
     * It simulates a step with a progress bar.
     */
    private function simulateStep(string $message, callable $action, string $successMessage): void
    {
        $this->info($message);
        $this->output->progressStart(100);
        usleep(500000);

        $action();

        $this->output->progressFinish();
        $this->info($successMessage);
        $this->newLine();
    }


    /**
     * Create an administrator user.
     *
     * This method asks the user to input the administrator's username, email and password.
     * Then, it creates a new administrator user.
     */
    private function createAdminUser(): void
    {
        $this->info('Por favor, ingrese los datos del administrador:');

        $username = $this->ask('Nombre de usuario del administrador');

        $email = $this->askValidEmail();

        $password = $this->askValidPassword();

        User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role_id' => RoleEnum::ADMIN->value,
            'status' => StatusEnum::ACTIVE->value,
        ]);
    }


    /**
     * Prompts the user to input a valid email address, validating the input.
     *
     * @return string The validated email address provided by the user.
     */
    private function askValidEmail(): string
    {
        return $this->askWithValidation(
            'Correo electrónico',
            fn ($input) => filter_var($input, FILTER_VALIDATE_EMAIL),
            'El correo ingresado no es válido. Por favor, inténtelo de nuevo.'
        );
    }


    /**
     * Ask for a valid password to the user.The password must meet the following requirements:
     * - It must contain between 8 and 12 characters.
     * - It must contain at least one uppercase and a tiny.
     * - It must contain at least one number.
     * - It must contain at least a special character (@, $,!, #, %, *,?, &).
     *
     * @return string The password entered by the user.
     */
    private function askValidPassword(): string
    {
        return $this->askWithValidation(
            'Contraseña (debe contener entre 8 y 12 caracteres, incluyendo mayúsculas, minúsculas, un número y un carácter especial)',
            fn ($input) => preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!#%*?&]).{8,12}$/', $input),
            'La contraseña no cumple con los requisitos. Por favor, inténtelo de nuevo.'
        );
    }

    /**
     * Prompt the user with a question and validates the input.
     *
     * @param string $question The question to ask the user.
     * @param callable $validation A callable that validates the user's input.
     * @param string $errorMessage The error message to display if validation fails.
     * @return string The validated user input.
     */
    private function askWithValidation(string $question, callable $validation, string $errorMessage): string
    {
        do {
            $input = $this->ask($question);
            if ($validation($input)) {
                return $input;
            }
            $this->error($errorMessage);
        } while (true);
    }
}
