# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is M-CRM (Medical Customer Relationship Management), a full-stack application for dental/medical clinics focused on lead management, patient journey tracking, and ROI optimization. The system tracks the complete patient funnel: visitor → lead → consultation → appointment → treatment → payment.

## Architecture

**Dual-stack structure:**
- `mcrm-backend/` - Laravel 12 API backend
- `m-crm-project/` - Next.js 14 + Material-UI frontend

**Core workflow:**
1. **Visit Collection**: UTM tracking, visitor sessions, and first-party client ID management
2. **Lead Management**: Multi-channel lead deduplication, rule-based scoring, and assignment
3. **Ticket System**: SLA-aware consultation tickets with communication tracking
4. **Analytics**: Real-time conversion funnel and ROI dashboards
5. **Audit Trail**: Complete GDPR-compliant activity logging

## Key Development Commands

### Backend (Laravel)
```bash
cd mcrm-backend

# Development with all services (recommended)
composer run dev
# This runs: server, queue worker, logs (Pail), and frontend concurrently

# Individual services
php artisan serve                    # API server
php artisan queue:listen --tries=1   # Background job processing
php artisan pail --timeout=0        # Real-time logs

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed    # Fresh database with test data

# Testing and linting
composer run test                    # PHPUnit tests
vendor/bin/pint                     # Laravel Pint code formatting
```

### Frontend (Next.js)
```bash
cd m-crm-project

npm run dev      # Development server
npm run build    # Production build
npm run start    # Production server
npm run lint     # ESLint
```

## Database Architecture

**Core entities with UUID primary keys:**
- `visits` - UTM/referrer tracking, session management
- `leads` - Patient information with email hashing and phone masking
- `tickets` - Consultation workflow with SLA tracking
- `appointments` - Scheduling with reminder automation
- `communications` - Multi-channel interaction history
- `users` - Role-based access (슈퍼관리자, 지점관리자, 상담매니저, 마케터, 의사)

**Important relationships:**
- Leads reference visits for attribution
- Tickets belong to leads and users (assignee)
- Foreign key constraints are added in separate migration (2025_09_25_000000_add_foreign_keys_to_leads_table.php)

## API Structure

All API routes are in `mcrm-backend/routes/api.php`:
- **Public**: `POST /api/collect/visit` for visitor tracking
- **Authenticated**: RESTful resources for leads, tickets, appointments, users
- **Dashboards**: Funnel analysis, channel performance, agent metrics
- **Authentication**: Laravel Sanctum with session-based SPAs

## Frontend Structure

**Next.js App Router with key pages:**
- `/` - Dashboard overview
- `/leads` - Lead management with detail views
- `/tickets` - Consultation ticket inbox
- `/dashboards/*` - Analytics (funnel, channel-pivot, agent-performance)
- `/appointments` - Scheduling interface

**State management:**
- React Context for auth (`AuthContext`)
- Real-time updates via Pusher (`RealtimeContext`)
- Material-UI components with Emotion styling

## Development Patterns

**Backend:**
- All models use UUID primary keys with custom `boot()` methods
- Auditable trait for compliance logging (`\App\Traits\Auditable`)
- Sensitive data (phone, email) uses hashing/masking in User model
- Queue-based SLA monitoring and reminder jobs

**Frontend:**
- TypeScript with strict typing
- Material-UI components with consistent theming
- Real-time data updates for ticket status changes
- Responsive design with mobile-first approach

**Data Flow:**
- Visitor tracking → Lead creation → Ticket assignment → Communication logging
- All foreign key relationships support soft deletes with `onDelete('set null')`
- Lead scoring combines channel attribution, page behavior, and time-based factors

## Key Configuration

**Laravel:** Standard .env setup with additional keys for:
- Sanctum stateful domains
- Queue driver (Redis recommended)
- Pusher credentials for real-time features

**Next.js:** API base URL configuration for backend communication

## Important Notes

- **Privacy compliance**: Phone numbers and emails are masked/hashed in User model
- **Multi-tenancy ready**: Clinic-based data isolation in user roles
- **SLA enforcement**: Background jobs monitor ticket response times
- **Lead deduplication**: Automatic merging based on phone/email matching
- **Campaign attribution**: Complete UTM parameter tracking with cost import capabilities