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

            // Migrar la base de datos
            $this->simulateStep('Migrando base de datos', function () {
                Artisan::call('migrate', ['--force' => true]);
            }, 'Base de datos migrada con éxito.');

            $this->newLine();

            // Correr los seeders
            $this->simulateStep('Poblando base de datos', function () {
                Artisan::call('db:seed', ['--force' => true]);
            }, 'Base de datos poblada con éxito.');

            $this->newLine();

            // Crear usuario administrador
            $this->simulateStep('Creando usuario administrador', function () {
                $this->createAdminUser();
            }, 'Usuario administrador creado con éxito.');

            $this->newLine();
            $this->info('La aplicación ha sido configurada correctamente.');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());

            Artisan::call('db:wipe');
        }
    }

    /**
     * Simula un paso con una barra de progreso.
     */
    private function simulateStep(string $message, callable $action, string $successMessage): void
    {
        $this->info($message);
        $this->output->progressStart(100);
        usleep(500000); // Pausa para simular progreso

        $action();

        $this->output->progressFinish();
        $this->info($successMessage);
        $this->newLine();
    }

    /**
     * Crea el usuario administrador.
     */
    private function createAdminUser(): void
    {
        $this->info('Por favor, ingrese los datos del administrador:');

        // Solicitar nombre
        $username = $this->ask('Nombre de usuario del administrador');

        // Solicitar email
        $email = $this->askValidEmail();

        // Solicitar contraseña
        $password = $this->askValidPassword();

        // Crear el usuario
        User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role_id' => RoleEnum::ADMIN->value,
            'status' => StatusEnum::ACTIVE->value,
        ]);
    }

    /**
     * Solicitar un correo electrónico válido.
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
     * Solicitar una contraseña válida con regex.
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
     * Solicitar entrada con validación.
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
