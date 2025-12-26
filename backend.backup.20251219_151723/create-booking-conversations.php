<?php

/**
 * Create Conversations for Beauty Bookings
 * ایجاد گفتگوها برای رزروهای زیبایی
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Creating conversations for beauty bookings...\n";
echo "ایجاد گفتگوها برای رزروهای زیبایی...\n\n";

try {
    if (!Schema::hasTable('conversations') || !Schema::hasTable('beauty_bookings')) {
        echo "❌ Required tables do not exist\n";
        exit(1);
    }

    // Get bookings without conversations
    $bookings = DB::table('beauty_bookings')
        ->whereNull('conversation_id')
        ->get();

    if ($bookings->isEmpty()) {
        echo "✅ All bookings already have conversations\n";
        exit(0);
    }

    echo "Found {$bookings->count()} bookings without conversations\n\n";

    $created = 0;
    foreach ($bookings as $booking) {
        // Get salon store to find vendor
        $salon = DB::table('beauty_salons')->where('id', $booking->salon_id)->first();
        if (!$salon) {
            echo "⚠️  Salon not found for booking {$booking->id}\n";
            continue;
        }

        $store = DB::table('stores')->where('id', $salon->store_id)->first();
        if (!$store || !$store->vendor_id) {
            echo "⚠️  Store or vendor not found for booking {$booking->id}\n";
            continue;
        }

        // Get or create UserInfo for customer
        $customer = DB::table('users')->where('id', $booking->user_id)->first();
        if (!$customer) {
            echo "⚠️  User not found for booking {$booking->id}\n";
            continue;
        }

        $customerInfo = DB::table('user_infos')->where('user_id', $booking->user_id)->first();
        if (!$customerInfo) {
            $customerInfoId = DB::table('user_infos')->insertGetId([
                'user_id' => $customer->id,
                'f_name' => $customer->f_name ?? 'Customer',
                'l_name' => $customer->l_name ?? 'User',
                'phone' => $customer->phone ?? null,
                'email' => $customer->email ?? null,
                'image' => $customer->image ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $customerInfo = (object)['id' => $customerInfoId];
        }

        // Get or create UserInfo for vendor
        $vendor = DB::table('vendors')->where('id', $store->vendor_id)->first();
        if (!$vendor) {
            echo "⚠️  Vendor not found for booking {$booking->id}\n";
            continue;
        }

        $vendorInfo = DB::table('user_infos')->where('vendor_id', $store->vendor_id)->first();
        if (!$vendorInfo) {
            $vendorInfoId = DB::table('user_infos')->insertGetId([
                'vendor_id' => $vendor->id,
                'f_name' => $vendor->f_name ?? 'Vendor',
                'l_name' => $vendor->l_name ?? 'User',
                'phone' => $vendor->phone ?? null,
                'email' => $vendor->email ?? null,
                'image' => $vendor->image ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $vendorInfo = (object)['id' => $vendorInfoId];
        }

        // Check if conversation already exists
        $existingConversation = DB::table('conversations')
            ->where(function($q) use ($customerInfo, $vendorInfo) {
                $q->where(function($q2) use ($customerInfo, $vendorInfo) {
                    $q2->where('sender_id', $customerInfo->id)
                       ->where('receiver_id', $vendorInfo->id);
                })->orWhere(function($q2) use ($customerInfo, $vendorInfo) {
                    $q2->where('sender_id', $vendorInfo->id)
                       ->where('receiver_id', $customerInfo->id);
                });
            })
            ->where('sender_type', 'customer')
            ->where('receiver_type', 'vendor')
            ->first();

        if ($existingConversation) {
            // Use existing conversation
            DB::table('beauty_bookings')
                ->where('id', $booking->id)
                ->update(['conversation_id' => $existingConversation->id]);
            echo "✅ Linked booking {$booking->id} to existing conversation {$existingConversation->id}\n";
            $created++;
            continue;
        }

        // Create new conversation
        $conversationId = DB::table('conversations')->insertGetId([
            'sender_id' => $customerInfo->id,
            'sender_type' => 'customer',
            'receiver_id' => $vendorInfo->id,
            'receiver_type' => 'vendor',
            'unread_message_count' => 0,
            'last_message_time' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Link booking to conversation
        DB::table('beauty_bookings')
            ->where('id', $booking->id)
            ->update(['conversation_id' => $conversationId]);

        echo "✅ Created conversation {$conversationId} for booking {$booking->id}\n";
        $created++;
    }

    echo "\n✅ Created/linked {$created} conversations\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

