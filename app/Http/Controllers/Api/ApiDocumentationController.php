<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiDocumentationController extends Controller
{
    public function index(): View
    {
        $endpoints = [
            ['method' => 'GET', 'path' => '/api/v1/health', 'description' => 'Health check endpoint'],
            ['method' => 'GET', 'path' => '/api/v1/hostels', 'description' => 'List hostels (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/hostels', 'description' => 'Create hostel'],
            ['method' => 'PATCH', 'path' => '/api/v1/hostels/{hostel}', 'description' => 'Update hostel'],
            ['method' => 'DELETE', 'path' => '/api/v1/hostels/{hostel}', 'description' => 'Delete hostel'],
            ['method' => 'GET', 'path' => '/api/v1/rooms', 'description' => 'List rooms (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/rooms', 'description' => 'Create room'],
            ['method' => 'PATCH', 'path' => '/api/v1/rooms/{room}', 'description' => 'Update room'],
            ['method' => 'DELETE', 'path' => '/api/v1/rooms/{room}', 'description' => 'Delete room'],
            ['method' => 'GET', 'path' => '/api/v1/students', 'description' => 'List students (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/students', 'description' => 'Create student'],
            ['method' => 'PATCH', 'path' => '/api/v1/students/{student}', 'description' => 'Update student'],
            ['method' => 'DELETE', 'path' => '/api/v1/students/{student}', 'description' => 'Delete student'],
            ['method' => 'GET', 'path' => '/api/v1/managers', 'description' => 'List managers (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/managers', 'description' => 'Create manager'],
            ['method' => 'PATCH', 'path' => '/api/v1/managers/{manager}', 'description' => 'Update manager'],
            ['method' => 'DELETE', 'path' => '/api/v1/managers/{manager}', 'description' => 'Delete manager'],
            ['method' => 'GET', 'path' => '/api/v1/bookings', 'description' => 'List bookings (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/bookings', 'description' => 'Create booking'],
            ['method' => 'PATCH', 'path' => '/api/v1/bookings/{booking}', 'description' => 'Update booking'],
            ['method' => 'GET', 'path' => '/api/v1/payments', 'description' => 'List payments (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/payments', 'description' => 'Create payment'],
            ['method' => 'PATCH', 'path' => '/api/v1/payments/{payment}', 'description' => 'Update payment'],
            ['method' => 'GET', 'path' => '/api/v1/complaints', 'description' => 'List complaints (paginated)'],
            ['method' => 'PATCH', 'path' => '/api/v1/complaints/{complaint}', 'description' => 'Update complaint'],
            ['method' => 'GET', 'path' => '/api/v1/assets', 'description' => 'List addon assets (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/assets', 'description' => 'Create addon asset'],
            ['method' => 'PATCH', 'path' => '/api/v1/assets/{asset}', 'description' => 'Update addon asset'],
            ['method' => 'DELETE', 'path' => '/api/v1/assets/{asset}', 'description' => 'Delete addon asset'],
            ['method' => 'GET', 'path' => '/api/v1/asset-issues', 'description' => 'List addon asset issues'],
            ['method' => 'PATCH', 'path' => '/api/v1/asset-issues/{assetIssue}', 'description' => 'Update addon asset issue'],
            ['method' => 'GET', 'path' => '/api/v1/asset-movements', 'description' => 'List addon asset movements'],
            ['method' => 'PATCH', 'path' => '/api/v1/asset-movements/{assetMovement}', 'description' => 'Update addon asset movement'],
            ['method' => 'GET', 'path' => '/api/v1/asset-subscriptions', 'description' => 'List addon intangible assets'],
            ['method' => 'POST', 'path' => '/api/v1/asset-subscriptions', 'description' => 'Create addon intangible asset'],
            ['method' => 'PATCH', 'path' => '/api/v1/asset-subscriptions/{assetSubscription}', 'description' => 'Update addon intangible asset'],
            ['method' => 'DELETE', 'path' => '/api/v1/asset-subscriptions/{assetSubscription}', 'description' => 'Delete addon intangible asset'],
        ];

        $webhookEvents = [
            'hostel.created',
            'hostel.updated',
            'hostel.deleted',
            'room.created',
            'room.updated',
            'room.deleted',
            'booking.created',
            'booking.cancelled',
            'booking.manager_approved',
            'booking.manager_rejected',
            'booking.manager_cancelled',
            'payment.completed',
            'complaint.created',
            'complaint.responded',
            'hostel_change.submitted',
            'hostel_change.manager_approved',
            'hostel_change.manager_rejected',
            'hostel_change.admin_approved',
            'hostel_change.admin_rejected',
            'asset.created',
            'asset.issue_reported',
            'asset.movement_requested',
            'asset.movement_receiving_decision',
            'asset.movement_approved',
            'asset.movement_rejected',
            'asset.subscription.created',
            'asset.subscription.updated',
            'asset.subscription.deleted',
            'asset.subscription.expiry_alert',
            'addon.activated',
            'addon.deactivated',
            'system.webhook_test',
        ];

        $webhookUserFields = [
            'id',
            'name',
            'first_name',
            'last_name',
            'email',
            'phone',
            'role',
            'hostel_id',
            'hostel_name',
            'id_number',
            'address',
            'guardian_name',
            'guardian_phone',
            'is_active',
            'is_admin_uploaded',
            'must_change_password',
            'profile_image_url',
            'extra_data',
            'created_at',
            'updated_at',
        ];

        return view('admin.api-docs.index', [
            'endpoints' => $endpoints,
            'webhookEvents' => $webhookEvents,
            'webhookUserFields' => $webhookUserFields,
            'openApiUrl' => route('admin.api.docs.openapi'),
        ]);
    }

    public function openApi(): JsonResponse
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Hostel Management API',
                'version' => '1.0.0',
                'description' => 'Third-party integration API for hostels, rooms, students, bookings, payments, and complaints.',
            ],
            'servers' => [
                ['url' => url('/')],
            ],
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                    'ApiKeyHeader' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-Key',
                    ],
                ],
            ],
            'security' => [
                ['BearerAuth' => []],
                ['ApiKeyHeader' => []],
            ],
            'paths' => [
                '/api/v1/health' => ['get' => ['summary' => 'Health check', 'responses' => ['200' => ['description' => 'OK']]]],
                '/api/v1/hostels' => [
                    'get' => ['summary' => 'List hostels', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create hostel', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/hostels/{hostel}' => [
                    'patch' => ['summary' => 'Update hostel', 'responses' => ['200' => ['description' => 'OK']]],
                    'delete' => ['summary' => 'Delete hostel', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/rooms' => [
                    'get' => ['summary' => 'List rooms', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create room', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/rooms/{room}' => [
                    'patch' => ['summary' => 'Update room', 'responses' => ['200' => ['description' => 'OK']]],
                    'delete' => ['summary' => 'Delete room', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/students' => [
                    'get' => ['summary' => 'List students', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create student', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/students/{student}' => [
                    'patch' => ['summary' => 'Update student', 'responses' => ['200' => ['description' => 'OK']]],
                    'delete' => ['summary' => 'Delete student', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/managers' => [
                    'get' => ['summary' => 'List managers', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create manager', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/managers/{manager}' => [
                    'patch' => ['summary' => 'Update manager', 'responses' => ['200' => ['description' => 'OK']]],
                    'delete' => ['summary' => 'Delete manager', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/bookings' => [
                    'get' => ['summary' => 'List bookings', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create booking', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/bookings/{booking}' => [
                    'patch' => ['summary' => 'Update booking', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/payments' => [
                    'get' => ['summary' => 'List payments', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create payment', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/payments/{payment}' => [
                    'patch' => ['summary' => 'Update payment', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/complaints' => [
                    'get' => ['summary' => 'List complaints', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/complaints/{complaint}' => [
                    'patch' => ['summary' => 'Update complaint', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/assets' => [
                    'get' => ['summary' => 'List addon assets', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create addon asset', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/assets/{asset}' => [
                    'patch' => ['summary' => 'Update addon asset', 'responses' => ['200' => ['description' => 'OK']]],
                    'delete' => ['summary' => 'Delete addon asset', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/asset-issues' => [
                    'get' => ['summary' => 'List addon asset issues', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/asset-issues/{assetIssue}' => [
                    'patch' => ['summary' => 'Update addon asset issue', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/asset-movements' => [
                    'get' => ['summary' => 'List addon asset movements', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/asset-movements/{assetMovement}' => [
                    'patch' => ['summary' => 'Update addon asset movement', 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/v1/asset-subscriptions' => [
                    'get' => ['summary' => 'List addon intangible assets', 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => 'Create addon intangible asset', 'responses' => ['201' => ['description' => 'Created']]],
                ],
                '/api/v1/asset-subscriptions/{assetSubscription}' => [
                    'patch' => ['summary' => 'Update addon intangible asset', 'responses' => ['200' => ['description' => 'OK']]],
                    'delete' => ['summary' => 'Delete addon intangible asset', 'responses' => ['200' => ['description' => 'OK']]],
                ],
            ],
        ];

        return response()->json($spec);
    }
}
