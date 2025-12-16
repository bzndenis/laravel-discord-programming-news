<?php

namespace App\Services;

use App\Jobs\ProcessFeatureUpdate;
use App\Jobs\ProcessSecuritySource;
use App\Models\FeatureUpdate;
use App\Models\SecurityAdvisory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DiscordInteractionService
{
    /**
     * Verify Discord request signature.
     */
    public function verifySignature(string $signature, string $timestamp, string $body): bool
    {
        $publicKey = config('discord.public_key');
        
        if (!$publicKey) {
            Log::error('Discord public key not configured');
            return false;
        }

        try {
            // Discord uses Ed25519 signature verification
            $message = $timestamp . $body;
            
            Log::debug('Signature verification attempt', [
                'timestamp' => $timestamp,
                'body_length' => strlen($body),
                'message_length' => strlen($message),
                'signature_length' => strlen($signature),
                'public_key_length' => strlen($publicKey),
            ]);
            
            $signatureBytes = @hex2bin($signature);
            $publicKeyBytes = @hex2bin($publicKey);
            
            if ($signatureBytes === false) {
                Log::error('Failed to decode signature hex');
                return false;
            }
            
            if ($publicKeyBytes === false) {
                Log::error('Failed to decode public key hex');
                return false;
            }
            
            $result = sodium_crypto_sign_verify_detached(
                $signatureBytes,
                $message,
                $publicKeyBytes
            );
            
            Log::info('Signature verification result', ['valid' => $result]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Signature verification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Handle Discord interaction.
     */
    public function handleInteraction(array $interaction): array
    {
        $type = $interaction['type'] ?? null;

        // Type 1: PING - Discord verification
        if ($type === 1) {
            return ['type' => 1]; // PONG
        }

        // Type 2: APPLICATION_COMMAND - Slash command
        if ($type === 2) {
            return $this->handleCommand($interaction);
        }

        return [
            'type' => 4, // CHANNEL_MESSAGE_WITH_SOURCE
            'data' => [
                'content' => '❌ Unknown interaction type.',
            ],
        ];
    }

    /**
     * Handle slash command.
     */
    protected function handleCommand(array $interaction): array
    {
        $commandName = $interaction['data']['name'] ?? null;

        return match ($commandName) {
            'security-update' => $this->handleSecurityUpdate($interaction),
            'feature-update' => $this->handleFeatureUpdate($interaction),
            'status' => $this->handleStatus($interaction),
            default => [
                'type' => 4,
                'data' => [
                    'content' => '❌ Unknown command.',
                ],
            ],
        };
    }

    /**
     * Handle /security-update command.
     */
    protected function handleSecurityUpdate(array $interaction): array
    {
        try {
            // Dispatch job
            ProcessSecuritySource::dispatch('manual_trigger');
            
            Log::info('Security update triggered manually via Discord', [
                'user' => $interaction['member']['user']['username'] ?? 'unknown',
            ]);

            return [
                'type' => 4, // CHANNEL_MESSAGE_WITH_SOURCE
                'data' => [
                    'embeds' => [[
                        'title' => '🔍 Security Scan Triggered',
                        'description' => 'Scanning for new security advisories...',
                        'color' => 3447003, // Blue
                        'footer' => [
                            'text' => 'Results will be posted here if new advisories are found.',
                        ],
                        'timestamp' => now()->toIso8601String(),
                    ]],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to trigger security update: ' . $e->getMessage());
            
            return [
                'type' => 4,
                'data' => [
                    'content' => '❌ Failed to trigger security scan. Please try again later.',
                    'flags' => 64, // EPHEMERAL - only visible to user
                ],
            ];
        }
    }

    /**
     * Handle /feature-update command.
     */
    protected function handleFeatureUpdate(array $interaction): array
    {
        try {
            // Dispatch job
            ProcessFeatureUpdate::dispatch();
            
            Log::info('Feature update triggered manually via Discord', [
                'user' => $interaction['member']['user']['username'] ?? 'unknown',
            ]);

            return [
                'type' => 4, // CHANNEL_MESSAGE_WITH_SOURCE
                'data' => [
                    'embeds' => [[
                        'title' => '🚀 Feature Scan Triggered',
                        'description' => 'Checking for new feature releases...',
                        'color' => 5763719, // Green
                        'footer' => [
                            'text' => 'Results will be posted here if new features are found.',
                        ],
                        'timestamp' => now()->toIso8601String(),
                    ]],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to trigger feature update: ' . $e->getMessage());
            
            return [
                'type' => 4,
                'data' => [
                    'content' => '❌ Failed to trigger feature scan. Please try again later.',
                    'flags' => 64, // EPHEMERAL
                ],
            ];
        }
    }

    /**
     * Handle /status command.
     */
    protected function handleStatus(array $interaction): array
    {
        try {
            $lastSecurityScan = Cache::get('bot_last_scan_completed');
            $lastFeatureScan = Cache::get('bot_last_feature_scan_completed');
            
            $securityCount = SecurityAdvisory::count();
            $featureCount = FeatureUpdate::count();
            
            $latestSecurity = SecurityAdvisory::latest('published_at')->first();
            $latestFeature = FeatureUpdate::latest('published_at')->first();

            $fields = [
                [
                    'name' => '📊 Total Security Advisories',
                    'value' => (string) $securityCount,
                    'inline' => true,
                ],
                [
                    'name' => '📦 Total Feature Updates',
                    'value' => (string) $featureCount,
                    'inline' => true,
                ],
            ];

            if ($lastSecurityScan) {
                $fields[] = [
                    'name' => '🔍 Last Security Scan',
                    'value' => $lastSecurityScan->diffForHumans(),
                    'inline' => false,
                ];
            }

            if ($lastFeatureScan) {
                $fields[] = [
                    'name' => '🚀 Last Feature Scan',
                    'value' => $lastFeatureScan->diffForHumans(),
                    'inline' => false,
                ];
            }

            if ($latestSecurity) {
                $fields[] = [
                    'name' => '🆕 Latest Security Advisory',
                    'value' => "[{$latestSecurity->framework_name}] {$latestSecurity->title}\n*Published: {$latestSecurity->published_at->diffForHumans()}*",
                    'inline' => false,
                ];
            }

            if ($latestFeature) {
                $fields[] = [
                    'name' => '🆕 Latest Feature Update',
                    'value' => "[{$latestFeature->source_name}] {$latestFeature->version}\n*Published: {$latestFeature->published_at->diffForHumans()}*",
                    'inline' => false,
                ];
            }

            return [
                'type' => 4, // CHANNEL_MESSAGE_WITH_SOURCE
                'data' => [
                    'embeds' => [[
                        'title' => '🤖 Bot Status',
                        'description' => 'Security & Feature Monitor Bot is operational.',
                        'color' => 3066993, // Green
                        'fields' => $fields,
                        'footer' => [
                            'text' => 'Last updated',
                        ],
                        'timestamp' => now()->toIso8601String(),
                    ]],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get status: ' . $e->getMessage());
            
            return [
                'type' => 4,
                'data' => [
                    'content' => '❌ Failed to retrieve status. Please try again later.',
                    'flags' => 64, // EPHEMERAL
                ],
            ];
        }
    }
}
