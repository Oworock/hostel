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
            ['method' => 'GET', 'path' => '/api/v1/bookings', 'description' => 'List bookings (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/bookings', 'description' => 'Create booking'],
            ['method' => 'PATCH', 'path' => '/api/v1/bookings/{booking}', 'description' => 'Update booking'],
            ['method' => 'GET', 'path' => '/api/v1/payments', 'description' => 'List payments (paginated)'],
            ['method' => 'POST', 'path' => '/api/v1/payments', 'description' => 'Create payment'],
            ['method' => 'PATCH', 'path' => '/api/v1/payments/{payment}', 'description' => 'Update payment'],
            ['method' => 'GET', 'path' => '/api/v1/complaints', 'description' => 'List complaints (paginated)'],
            ['method' => 'PATCH', 'path' => '/api/v1/complaints/{complaint}', 'description' => 'Update complaint'],
        ];

        $webhookEvents = [
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
            'system.webhook_test',
        ];

        return view('admin.api-docs.index', [
            'endpoints' => $endpoints,
            'webhookEvents' => $webhookEvents,
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
            ],
        ];

        return response()->json($spec);
    }
}
