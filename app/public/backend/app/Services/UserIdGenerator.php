<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\User;

class UserIdGenerator
{
    /**
     * Generate a custom user ID in format: USR-username-shortUUID-numberProgression
     * Example: USR-john-550e8400-e29b-41d4-a716-1
     *
     * @param string $username
     * @return string
     */
    public static function generateCustomId(string $username): string
    {
        // Generate UUID and shorten it (keep first 3 segments: 550e8400-e29b-41d4-a716)
        $fullUuid = Str::uuid();
        $uuidParts = explode('-', $fullUuid);
        $shortUuid = implode('-', array_slice($uuidParts, 0, 4));

        // Get the next progression number
        $progression = self::getNextProgression();

        // Create custom ID
        $customId = "USR-{$username}-{$shortUuid}-{$progression}";

        return $customId;
    }

    /**
     * Get the next progression number for user IDs
     *
     * @return int
     */
    private static function getNextProgression(): int
    {
        // Count existing users to get the next progression number
        $count = User::count();
        return $count + 1;
    }
}
