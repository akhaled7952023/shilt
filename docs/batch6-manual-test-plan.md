# Batch 6 — Manual Test Plan: Expiry Notifications & Admin Inbox Filter

Run these tests in order. Each test is independent unless noted.

---

## TEST 1 — Expiry notification uses configured thresholds, not hardcoded values

**Goal:** Verify the command reads `notify_document_expiry_days` from `system_settings`.

**Steps:**
1. Check the current setting:
   ```
   php artisan tinker --execute="echo App\Models\SystemSetting::get('notify_document_expiry_days');"
   ```
   Expected: `30,14,7`

2. Temporarily change it to a single value (e.g. `60`) and run the command:
   ```
   php artisan tinker --execute="App\Models\SystemSetting::set('notify_document_expiry_days', '60');"
   php artisan notify:expiry-alerts
   ```
   If there are no documents expiring within 60 days, Sent=0 is expected.

3. Restore the original value:
   ```
   php artisan tinker --execute="App\Models\SystemSetting::set('notify_document_expiry_days', '30,14,7');"
   ```

**Pass:** Command uses the value from the database. No hardcoded values.

---

## TEST 2 — Multi-threshold: each threshold fires exactly once per document

**Goal:** Verify [30, 14, 7] each triggers independently; re-running the same threshold is blocked.

**Steps:**
1. Find a document expiring in ~15 days:
   ```
   php artisan tinker --execute="DB::table('delegate_documents')->where('document_type_id', 2)->orderBy('expiry_date')->get(['id','expiry_date','delegate_id']);"
   ```

2. If none exists, temporarily set a document's expiry_date to today + 15 days for testing:
   ```
   php artisan tinker --execute="DB::table('delegate_documents')->where('id', 1)->update(['expiry_date' => now()->addDays(15)->toDateString()]);"
   ```

3. Clear existing notifications for this document:
   ```
   php artisan tinker --execute="DB::table('notifications')->where('notifiable_type','delegate_document')->where('notifiable_id',1)->delete();"
   ```

4. Run the command:
   ```
   php artisan notify:expiry-alerts
   ```
   Expected: `Sent: 1` (threshold 14 fires because daysLeft=15 ≤ 14 is false — actually 15 > 14 so threshold=30 fires)

   Actually with daysLeft=15: resolveThreshold(15, [7,14,30]) = 30 (first T where 15 ≤ T).
   So threshold=30 fires. Expected: `Sent: 1, Already notified: 0`.

5. Re-run immediately:
   Expected: `Sent: 0, Already notified: 1`. Dedup blocked the re-send.

6. Advance the document to 10 days from now:
   ```
   php artisan tinker --execute="DB::table('delegate_documents')->where('id', 1)->update(['expiry_date' => now()->addDays(10)->toDateString()]);"
   ```
   Run the command:
   Expected: `Sent: 1` (threshold=14 fires — different threshold, not deduped).

7. Advance to 5 days from now and run again:
   Expected: `Sent: 1` (threshold=7 fires).

8. Run again at 5 days:
   Expected: `Sent: 0` (threshold=7 already sent).

**Pass:** Three separate notifications sent, one per threshold level, never duplicated at the same threshold.

---

## TEST 3 — Correct recipient: admin sees it; delegate does not

**Goal:** Verify notifications go to `recipient_type='admin'`, not `recipient_type='delegate'`.

**Steps:**
1. After running `notify:expiry-alerts`, inspect the latest notification row:
   ```
   php artisan tinker --execute="\$n = DB::table('notifications')->where('category','iqama_expiring')->latest('id')->first(); echo \$n->recipient_type . ' / ' . \$n->recipient_id;"
   ```
   Expected: `admin / <admin-user-id>`

2. Log into the admin dashboard → check the bell icon badge.
   Expected: badge shows unread count (≥ 1).

3. Click the bell → navigate to the notification inbox.
   Expected: at least one notification in the "المستندات" (Documents) tab.

4. Log into the delegate portal.
   Expected: delegate does NOT see expiry notifications (those go to admins only).

**Pass:** `recipient_type = 'admin'`. Admin badge and inbox show the notification.

---

## TEST 4 — Arabic document name in notification title (no raw JSON)

**Goal:** Verify the document name is parsed from JSON and shows Arabic text.

**Steps:**
1. Check the `document_types` name column:
   ```
   php artisan tinker --execute="DB::table('document_types')->pluck('name', 'id');"
   ```
   Expected: values are JSON strings like `{"ar":"إقامة","en":"Iqama"}`.

2. After running `notify:expiry-alerts`, check the title:
   ```
   php artisan tinker --execute="echo DB::table('notifications')->where('category','iqama_expiring')->latest('id')->value('title');"
   ```
   Expected: `انتهاء صلاحية إقامة — <delegate name>` (Arabic text, no curly braces or raw JSON).

**Pass:** Title contains Arabic document name, not raw `{"ar":"...","en":"..."}` JSON string.

---

## TEST 5 — Soft-deleted delegates are excluded

**Goal:** Verify soft-deleted delegates do not trigger notifications.

**Steps:**
1. Find the delegate_id being tested, then soft-delete them:
   ```
   php artisan tinker --execute="DB::table('delegates')->where('id', 14)->update(['deleted_at' => now()]);"
   ```

2. Clear any existing notifications for their documents:
   ```
   php artisan tinker --execute="DB::table('notifications')->where('notifiable_type','delegate_document')->where('data->delegate_id', 14)->delete();"
   ```

3. Run the command:
   ```
   php artisan notify:expiry-alerts
   ```
   Expected: `Documents scanned: 0` (or document is excluded), `Sent: 0`.

4. Restore the delegate:
   ```
   php artisan tinker --execute="DB::table('delegates')->where('id', 14)->update(['deleted_at' => null]);"
   ```

**Pass:** Soft-deleted delegate's documents are not processed.

---

## TEST 6 — Admin inbox filter tabs work correctly

**Goal:** Verify the filter tabs in the admin inbox show only the correct category of notification.

**Steps:**
1. Log in as admin and navigate to: `/dashboard/support/notifications`
   Expected: "الكل" tab is active. All notifications shown.

2. Click "المستندات" tab.
   Expected: URL changes to `?filter=documents`. Only iqama/passport/driving-license/vehicle notifications shown.

3. Click "مركز الدعم" tab.
   Expected: Only ticket_new/ticket_reply/ticket_closed/ticket_reopened notifications shown.

4. Click "التسويات" tab.
   Expected: Only settlement_published/settlement_viewed notifications shown.

5. Click "الإجازات" tab.
   Expected: "لا توجد إشعارات" (empty state) — no leave notifications defined yet.

6. Click "الكل" to return to all notifications.
   Verify the pagination keeps `?filter=all` in the URL.

**Pass:** Each tab filters by the correct category set. Tabs navigate correctly. Pagination preserves the active filter.

---

## Quick Verification Commands

```bash
# Check scheduler is registered
php artisan schedule:list

# Run command in debug mode (APP_DEBUG=true in .env)
php artisan notify:expiry-alerts

# Check last notification written
php artisan tinker --execute="print_r((array) DB::table('notifications')->latest('id')->first());"

# Count unread admin notifications
php artisan tinker --execute="echo DB::table('notifications')->where('recipient_type','admin')->whereNull('read_at')->count();"
```
