<?php

namespace App\Console\Commands;

use App\Services\ContentSyncService;
use Illuminate\Console\Command;

class SyncContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync content from all providers (JSON and XML)';

    /**
     * Execute the console command.
     */
    public function handle(ContentSyncService $syncService)
    {
        $this->info('Starting content synchronization...');
        
        $result = $syncService->syncAllProviders();
        
        if ($result['success']) {
            $this->info("✅ Successfully synced {$result['total_synced']} items from all providers");
            
            foreach ($result['provider_results'] as $providerResult) {
                if ($providerResult['success']) {
                    $this->line("   - {$providerResult['provider']}: {$providerResult['synced_count']} items");
                } else {
                    $this->error("   - {$providerResult['provider']}: Failed - {$providerResult['error']}");
                }
            }
        } else {
            $this->error('❌ Sync completed with errors:');
            foreach ($result['errors'] as $error) {
                $this->error("   - {$error}");
            }
        }
        
        // Store sync status
        $syncService->storeLastSyncStatus($result);
        
        return $result['success'] ? 0 : 1;
    }
}
