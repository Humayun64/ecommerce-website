<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

/**
 * The shop's outgoing email, set from the admin panel instead of the .env
 * file. The password is stored encrypted and only decrypted here.
 */
class MailSettings
{
    /** The address used when the web server sends and none was typed in. */
    public function defaultFrom(): string
    {
        $host = preg_replace('/^www\./i', '', (string) parse_url((string) config('app.url'), PHP_URL_HOST));

        // A made-up or local host would be refused by every inbox.
        return $host !== '' && str_contains($host, '.') && ! filter_var($host, FILTER_VALIDATE_IP)
            ? 'no-reply@' . strtolower($host)
            : '';
    }

    /** @return array{mode:string, on:bool, host:string, port:int, security:string, username:string, password:string, from_address:string, from_name:string, admin_copy:bool, admin_to:string} */
    public function all(): array
    {
        $password = (string) Setting::get('mail_password', '');

        if ($password !== '') {
            try {
                $password = Crypt::decryptString($password);
            } catch (\Throwable $e) {
                $password = '';
            }
        }

        return [
            // "server": the web server sends it, nothing to set up.
            // "smtp":   a real mailbox, with a password.
            'mode'         => Setting::get('mail_mode', 'smtp') === 'server' ? 'server' : 'smtp',
            'on'           => Setting::get('mail_on', '0') === '1',
            'host'         => trim((string) Setting::get('mail_host', '')),
            'port'         => (int) Setting::get('mail_port', 465),
            'security'     => Setting::get('mail_security', 'ssl') === 'tls' ? 'tls' : 'ssl',
            'username'     => trim((string) Setting::get('mail_username', '')),
            'password'     => $password,
            'from_address' => trim((string) Setting::get('mail_from_address', '')),
            'from_name'    => trim((string) Setting::get('mail_from_name', '')),
            'admin_copy'   => Setting::get('mail_admin_copy', '0') === '1',
            'admin_to'     => trim((string) Setting::get('mail_admin_to', '')),
        ];
    }

    public function ready(): bool
    {
        $s = $this->all();

        if ($s['mode'] === 'server') {
            return $s['on'] && function_exists('mail') && $this->fromFor($s) !== '';
        }

        return $s['on'] && $s['host'] !== '' && $s['username'] !== '' && $s['password'] !== '' && $s['from_address'] !== '';
    }

    public function save(array $input): void
    {
        $pairs = [
            'mail_mode'         => ($input['mode'] ?? 'smtp') === 'server' ? 'server' : 'smtp',
            'mail_on'           => ! empty($input['on']) ? '1' : '0',
            'mail_host'         => trim((string) ($input['host'] ?? '')),
            'mail_port'         => (string) (int) ($input['port'] ?? 465),
            'mail_security'     => ($input['security'] ?? 'ssl') === 'tls' ? 'tls' : 'ssl',
            'mail_username'     => trim((string) ($input['username'] ?? '')),
            'mail_from_address' => trim((string) ($input['from_address'] ?? '')),
            'mail_from_name'    => trim((string) ($input['from_name'] ?? '')),
            'mail_admin_copy'   => ! empty($input['admin_copy']) ? '1' : '0',
            'mail_admin_to'     => trim((string) ($input['admin_to'] ?? '')),
        ];

        // An empty box means "keep the password already saved".
        if ((string) ($input['password'] ?? '') !== '') {
            $pairs['mail_password'] = Crypt::encryptString((string) $input['password']);
        }

        Setting::put($pairs);
    }

    /**
     * Point Laravel's mailer at these settings for this request. Called
     * once when the app starts; until the panel is filled in and switched
     * on, whatever .env says is left alone.
     */
    public function apply(): void
    {
        if (! $this->ready()) {
            return;
        }

        $s    = $this->all();
        $name = $s['from_name'] !== '' ? $s['from_name'] : (Setting::get('store_name') ?: config('app.name'));

        if ($s['mode'] === 'server') {
            config([
                'mail.default'         => 'phpmail',
                'mail.mailers.phpmail' => ['transport' => 'phpmail'],
                'mail.from'            => ['address' => $this->fromFor($s), 'name' => $name],
            ]);

            return;
        }

        config([
            'mail.default'      => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                // smtps = encrypted from the first byte (port 465);
                // smtp  = starts plain and upgrades with STARTTLS (port 587).
                'scheme'    => $s['security'] === 'ssl' ? 'smtps' : 'smtp',
                'host'      => $s['host'],
                'port'      => $s['port'],
                'username'  => $s['username'],
                'password'  => $s['password'],
                'timeout'   => 15,
            ],
            'mail.from' => [
                'address' => $s['from_address'],
                'name'    => $name,
            ],
        ]);
    }

    private function fromFor(array $s): string
    {
        return $s['from_address'] !== '' ? $s['from_address'] : $this->defaultFrom();
    }

    /** Where a copy of each new order goes, if that is switched on. */
    public function adminCopyTo(): ?string
    {
        $s = $this->all();

        return $s['admin_copy'] && filter_var($s['admin_to'], FILTER_VALIDATE_EMAIL) ? $s['admin_to'] : null;
    }
}
