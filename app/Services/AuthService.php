<?php
declare(strict_types=1);

namespace App\Services;

use HivePHP\Database;
use HivePHP\Configs;
use App\Models\User;
use HivePHP\Security\CookieManager;

class AuthService
{
    protected Database $db;
    protected User $userModel;
    protected CookieManager $cookies;
    protected array $config;

    public function __construct(Database $db, User $userModel, CookieManager $cookies)
    {
        $this->db = $db;
        $this->userModel = $userModel;
        $this->cookies = $cookies;
        $this->config = Configs::get('auth', []);
    }

    /**
     * Попытка логина. Возвращает user array при успехе, иначе null.
     * $remember — чекбокс "запомнить меня"
     */
    public function attempt(string $email, string $password, bool $remember = false): ?array
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        // password needs rehash?
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $new = password_hash($password, PASSWORD_DEFAULT);
            $this->db->execute("UPDATE users SET password = :pwd WHERE id = :id", ['pwd' => $new, 'id' => $user['id']]);
        }

        // Создаём php-сессию
        $_SESSION['user_id'] = (int)$user['id'];

        // Обновляем last login
        $this->userModel->updateLastLogin((int)$user['id']);

        // Создаём запись в sessions (опционально)
        $this->createDbSession((int)$user['id']);

        // Remember me
        if ($remember) {
            $this->createRememberToken((int)$user['id']);
        }

        return $user;
    }

    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        // Удалить db session (по session_id) — если используешь
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            if ($sessionId) {
                $this->db->execute("DELETE FROM sessions WHERE session_id = :sid", ['sid' => $sessionId]);
            }
        }

        // Удалить remember token, если есть
        $this->clearRememberCookie();

        // Убить PHP сессию
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public function id(): ?int
    {
        $user = $this->user();
        return $user['id'] ?? null;
    }

    public function user(): ?array
    {
        if (isset($_SESSION['user_id'])) {
            $u = $this->userModel->findById((int)$_SESSION['user_id']);
            if ($u) return $u;
        }

        // Попытка восстановить по remember cookie
        $user = $this->loginFromRemember();
        return $user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    /* ======= sessions table helper ====== */

    protected function createDbSession(int $userId): void
    {
        // session must be started by bootstrap/app already
        $sid = session_id() ?: bin2hex(random_bytes(16));
        if (!session_id()) {
            session_id($sid);
            // don't call session_start here if bootstrap already did
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $created = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + ($this->config['session_lifetime'] ?? 3600));

        // вставляем (ON DUPLICATE возможен)
        $this->db->insert(
            "INSERT INTO sessions (session_id, user_id, ip, user_agent, created_at, expires_at)
            VALUES (:sid, :user_id, :ip, :ua, :created, :expires)
            ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), ip = VALUES(ip), user_agent = VALUES(user_agent), created_at = VALUES(created_at), expires_at = VALUES(expires_at)",
            ['sid' => $sid, 'user_id' => $userId, 'ip' => $ip, 'ua' => $ua, 'created' => $created, 'expires' => $expires]
        );
    }

    /* ======= Remember token =======
       Используем selector:validator. В куке храним selector:validator_base64
       В БД — selector, hash(validator)
    */

    protected function createRememberToken(int $userId): void
    {
        $selector = rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
        $validator = bin2hex(random_bytes(32)); // hex validator (64 chars)
        $validatorHash = hash('sha256', $validator);

        $expiresSeconds = $this->config['remember_lifetime'] ?? (60 * 60 * 24 * 30); // 30 days by default
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresSeconds);

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $created = date('Y-m-d H:i:s');

        // Сохраняем в БД
        $this->db->insert(
            "INSERT INTO remember_tokens (selector, validator_hash, user_id, ip, user_agent, created_at, expires_at)
             VALUES (:selector, :vhash, :user_id, :ip, :ua, :created, :expires)",
            ['selector' => $selector, 'vhash' => $validatorHash, 'user_id' => $userId, 'ip' => $ip, 'ua' => $ua, 'created' => $created, 'expires' => $expiresAt]
        );

        // Ставим куку: value selector:validator (validator в base64 чтобы безопасно хранить в куке)
        $cookieValue = $selector . ':' . base64_encode(hex2bin($validator)); // convert hex to raw bytes then base64
        $cookieName = $this->config['cookie_name'] ?? 'remember_me';
        $this->cookies->set($cookieName, $cookieValue, (int)($expiresSeconds / 60)); // minutes

        // Опционально можно ограничивать количество remember токенов на пользователя и удалять старые
    }

    protected function loginFromRemember(): ?array
    {
        $cookieName = $this->config['cookie_name'] ?? 'remember_me';
        if (!$this->cookies->has($cookieName)) {
            return null;
        }

        $value = $this->cookies->get($cookieName);
        if (!is_string($value)) return null;

        // format: selector:base64(validator_raw)
        $parts = explode(':', $value);
        if (count($parts) !== 2) {
            $this->clearRememberCookie();
            return null;
        }

        [$selector, $validatorB64] = $parts;
        $validatorRaw = base64_decode($validatorB64, true);
        if ($validatorRaw === false) {
            $this->clearRememberCookie();
            return null;
        }

        // convert raw validator to hex (we stored hex when creating)
        $validatorHex = bin2hex($validatorRaw);
        $validatorHash = hash('sha256', $validatorHex);

        // find in DB by selector
        $row = $this->db->fetch(
            "SELECT * FROM remember_tokens WHERE selector = :selector LIMIT 1",
            ['selector' => $selector]
        );

        if (!$row) {
            $this->clearRememberCookie();
            return null;
        }

        // check expiry
        if (strtotime($row['expires_at']) < time()) {
            // удаляем запись
            $this->db->execute("DELETE FROM remember_tokens WHERE selector = :selector", ['selector' => $selector]);
            $this->clearRememberCookie();
            return null;
        }

        // безопасное сравнение хэшей
        if (!hash_equals($row['validator_hash'], $validatorHash)) {
            // ВАЖНО: если валидатор не совпал — возможная атака. Удалим все токены этого селектора/пользователя.
            $this->db->execute("DELETE FROM remember_tokens WHERE selector = :selector OR user_id = :user_id", ['selector' => $selector, 'user_id' => $row['user_id']]);
            $this->clearRememberCookie();
            return null;
        }

        // Всё ок — логиним пользователя, создаём новую PHP сессию (rotate)
        $_SESSION['user_id'] = (int)$row['user_id'];
        $this->userModel->updateLastLogin((int)$row['user_id']);

        // Перегенерируем remember token (rotate), удалим старый
        $this->db->execute("DELETE FROM remember_tokens WHERE selector = :selector", ['selector' => $selector]);
        $this->createRememberToken((int)$row['user_id']);

        // Создать/обновить sessions запись
        $this->createDbSession((int)$row['user_id']);

        return $this->userModel->findById((int)$row['user_id']);
    }

    protected function clearRememberCookie(): void
    {
        $cookieName = $this->config['cookie_name'] ?? 'remember_me';
        // Удалить запись из БД по селектору, если кука есть
        $val = $this->cookies->get($cookieName);
        if (is_string($val)) {
            $parts = explode(':', $val);
            if (count($parts) === 2) {
                $selector = $parts[0];
                $this->db->execute("DELETE FROM remember_tokens WHERE selector = :selector", ['selector' => $selector]);
            }
        }

        $this->cookies->delete($cookieName);
    }
}
