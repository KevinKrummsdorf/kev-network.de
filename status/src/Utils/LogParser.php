<?php
namespace App\Utils;

class LogParser
{
    public static function maskIpv4(string $ip): string
    {
        $parts = explode('.', $ip);
        return count($parts) === 4 ? "{$parts[0]}.{$parts[1]}.xxx.xxx" : 'xxx.xxx.xxx.xxx';
    }

    public static function maskIpv6(string $ip): string
    {
        $parts = explode(':', $ip);
        if (count($parts) === 8) {
            for ($i = 4; $i < 8; $i++) {
                $parts[$i] = 'xxxx';
            }
            return implode(':', $parts);
        }
        return 'xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx';
    }

public static function parseUpdates(string $logfile, int $max = 10): array
{
    if (!file_exists($logfile)) return [];

    $lines = array_reverse(file($logfile));
    $entries = [];
    $entry = [];
    $collecting = false;

    foreach ($lines as $line) {
        $line = trim($line);

        if (str_starts_with($line, '====') && str_contains($line, 'Update abgeschlossen')) {
            $collecting = true;
            continue;
        }

        if ($collecting) {
            if (str_contains($line, 'dynv6 erfolgreich aktualisiert')) {
                // Neuer Parser: maschinenlesbares Datum (2025-06-20 17:02:32)
                $entry['date'] = trim(substr($line, 0, 19)); // exakt 19 Zeichen (Y-m-d H:i:s)
            } elseif (str_starts_with($line, 'IPv4')) {
                $entry['ipv4'] = trim(explode('=', $line)[1] ?? '');
            } elseif (str_starts_with($line, 'IPv6')) {
                $entry['ipv6'] = trim(explode('=', $line)[1] ?? '');
            } elseif (str_starts_with($line, 'Antwort')) {
                $entry['response'] = trim(explode(':', $line, 2)[1] ?? '');
            } elseif (str_starts_with($line, '====') && str_contains($line, 'Update gestartet')) {
                if (!empty($entry)) {
                    $entries[] = $entry;
                    $entry = [];
                    if (count($entries) >= $max) break;
                }
                $collecting = false;
            }
        }
    }

    return $entries;
}


}
