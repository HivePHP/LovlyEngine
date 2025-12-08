<?php
declare(strict_types=1);

namespace app\Http\Controllers;

use App\Exceptions\RegistrationException;
use App\Services\AuthService;
use HivePHP\Controller;
use HivePHP\Validation\Exceptions\ValidationException;
use HivePHP\Validation\Validator;
use JetBrains\PhpStorm\NoReturn;
use HivePHP\Http\Request;
use HivePHP\Http\Response;

class AuthController extends Controller
{
    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                throw new RegistrationException("Некорректный JSON");
            }

            // Валидация
            $clean = Validator::make($data, [
                'name'            => 'required|string|min:2|max:30|alpha',
                'surname'         => 'required|string|min:2|max:30|alpha',
                'email'           => 'required|email|max:100',
                'password_first'  => 'required|min:6|max:50',
                'password_second' => 'required|same:password_first',
                'sex'             => 'required|in:male,female',
                'country'         => 'required|string|min:2|max:50|alpha_space',
                'city'            => 'required|string|min:2|max:50|alpha_space',
                'day'             => 'required|integer',
                'month'           => 'required|integer',
                'year'            => 'required|integer|date_valid:day,month,year',
            ])->validate()->clean();

            if ($this->userModel->emailExists($clean['email'])) {
                throw new RegistrationException([
                    'email' => 'Email уже используется'
                ]);
            }

            $clean['password'] = password_hash($clean['password_first'], PASSWORD_DEFAULT);

            unset($clean['password_first'], $clean['password_second']);

            $clean['created_at'] = date('Y-m-d H:i:s');

            $userId = $this->userModel->createUser($clean);

            $auth = $this->container->get('auth');
            $auth->attempt($clean['email'], $dataPasswordPlain ?? '', (bool)($data['remember'] ?? false));

            $_SESSION['user_id'] = $userId;
            $this->userModel->updateLastLogin($userId);

            jsonResponse(["status" => "ok", "uid" => $userId]);

        } catch (ValidationException $v) {
            jsonResponse(['status' => 'err', 'errors' => $v->errors()]);
        } catch (RegistrationException $e) {
            jsonResponse([
                'status' => 'err',
                'errors' => $e->getErrors()
            ]);
        }
    }

    #[NoReturn]
    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = trim($data['email'] ?? '');
        $pass = $data['password'] ?? '';
        $remember = !empty($data['remember']);

        // Базовая валидация
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['status'=>'err','errors'=>['email'=>'Некорректный Email']]);
        }

        if (!$pass) {
            jsonResponse(['status'=>'err','errors'=>['password'=>'Введите пароль']]);
        }

        /** @var AuthService $auth */
        $auth = $this->container->get('auth');
        $user = $auth->attempt($email, $pass, $remember);

        if (!$user) {
            jsonResponse(['status'=>'err','errors'=>['general'=>'Неверный email или пароль']]);
        }

        jsonResponse(['status'=>'ok','redirect'=>'/']);
    }

    #[NoReturn]
    public function logout(): void
    {
        /** @var AuthService $auth */
        $auth = $this->container->get('auth');
        $auth->logout();
        // редирект
        header("Location: /");
        exit;
    }
}
