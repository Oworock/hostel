# Frontend Click-Through Localization Checklist (Student / Manager / Public)

## Scope
- Panels: `public`, `student`, `manager`
- Goal: detect untranslated text that appears only under runtime conditions (status values, fallbacks, empty states, conditional alerts)

## Public Flow Checklist
1. Open `book-rooms` as guest.
- Verify guest-only actions (`Sign up`, `Log in`) are translated.
- Verify empty results state is translated.
2. Open `book-rooms/{room}/book`.
- Verify conditional prompt and CTA labels are translated.

## Student Flow Checklist
1. Open `student/bookings/available` with no blocking booking.
- Verify room cards and filter labels are translated.
2. Open `student/bookings/available` with pending booking.
- Verify pending-block alert and CTA labels are translated.
3. Open `student/bookings/available` with active booking.
- Verify active-block alert and change-request CTAs are translated.
4. Open `student/bookings` with mixed statuses.
- Verify status chip text is translated (approved/pending/rejected/completed/cancelled).
- Verify null-date fallback text is translated.
5. Open `student/bookings/{booking}`.
- Verify payment method/status and null-date fallback text are translated.
6. Open `student/payments` and `student/payments/index`.
- Verify outstanding badge text and table status text are translated.
- Verify fallback values (`N/A`, `Pending`) are translated.
7. Open `student/hostel-change-requests` and `student/room-change-requests`.
- Verify status text and nullable room/hostel fallbacks are translated.
8. Open `student/complaints` with multiple statuses.
- Verify complaint status chips are translated.
9. Open `student/notifications` with one notification lacking title.
- Verify fallback title text is translated.
10. Open `student/dashboard` with and without active booking.
- Verify fallback fields (`N/A`, `TBD`, host fallback) and status labels are translated.

## Manager Flow Checklist
1. Open `manager/bookings` and `manager/bookings/{booking}`.
- Verify status labels and nullable fallbacks are translated.
2. Open `manager/payments` and `manager/payments/index`.
- Verify payment status labels and nullable date/room fallbacks are translated.
3. Open `manager/complaints`.
- Verify complaint status labels are translated.
4. Open `manager/hostel-change-requests` and `manager/room-change-requests`.
- Verify request status labels and nullable room/hostel fallbacks are translated.
5. Open `manager/students` with students having/without active booking.
- Verify dynamic status text and fallback values are translated.
6. Open `manager/notifications` with one notification lacking title.
- Verify fallback title text is translated.
7. Open `manager/dashboard` with mixed booking/payment/complaint statuses.
- Verify status labels rendered from model values are translated.

## Logged Untranslated Runtime Text (Conditional)

### High Priority
1. Notification fallback title stays English when `title` missing.
- `resources/views/student/notifications/index.blade.php:20`
- `resources/views/manager/notifications/index.blade.php:20`
- Current: `?? 'Notification'`

2. Status labels rendered via raw enum/string transform (not translated).
- `resources/views/student/bookings/index.blade.php:34`
- `resources/views/student/bookings/show.blade.php:31`
- `resources/views/student/complaints.blade.php:71`
- `resources/views/student/hostel-change/index.blade.php:60`
- `resources/views/student/room-change/index.blade.php:72`
- `resources/views/student/payments/index.blade.php:93`
- `resources/views/manager/bookings/index.blade.php:37`
- `resources/views/manager/bookings/show.blade.php:55`
- `resources/views/manager/complaints/index.blade.php:49`
- `resources/views/manager/hostel-change/index.blade.php:25`
- `resources/views/manager/room-change/index.blade.php:24`
- `resources/views/manager/payments/index.blade.php:61`
- `resources/views/manager/dashboard.blade.php:119`
- `resources/views/manager/dashboard.blade.php:193`
- Current pattern: `ucfirst(...)` / `ucwords(str_replace('_', ' ', ...))`

### Medium Priority
3. Nullable fallback values are hardcoded English.
- `resources/views/student/dashboard.blade.php:47` (`'N/A'`)
- `resources/views/student/dashboard.blade.php:59` (`'TBD'`)
- `resources/views/student/dashboard.blade.php:74` (`'N/A'`)
- `resources/views/student/payments.blade.php:63` (`'Pending'`)
- `resources/views/student/payments.blade.php:86` (`N/A` text)
- `resources/views/student/payments/index.blade.php:102` (`'N/A'`)
- `resources/views/student/payments/index.blade.php:107` (`N/A` text)
- `resources/views/student/bookings/index.blade.php:45` (`'TBD'`)
- `resources/views/student/bookings/receipt.blade.php:163` (`'N/A'`)
- `resources/views/student/bookings/receipt.blade.php:179` (`'N/A'`)
- `resources/views/manager/students/index.blade.php:61` (`'N/A'`)
- `resources/views/manager/payments.blade.php:59` (`'N/A'`)
- `resources/views/manager/payments.blade.php:71` (`'N/A'`)

4. Conditional count/status snippets still include English literal.
- `resources/views/student/payments/index.blade.php:26`
- Current: `{{ $outstandingBookings->count() }} pending`

### Lower Priority
5. Runtime-composed labels with untranslated fragments.
- `resources/views/student/dashboard.blade.php:103` (`?? 'Hostel'`, plus `Room` literal)
- `resources/views/manager/students/index.blade.php:81` (`Room {{ ... }}`)
- `resources/views/student/payments/index.blade.php:37` (`Booking #... • Room ...`)

## Result
- Checklist prepared for click-through QA.
- Conditional-runtime untranslated text has been logged with file/line references for remediation.
