# EventZon - Event Management Platform

## Overview

EventZon is a comprehensive event booking and management platform built for Cameroon, featuring event discovery, booking management, payment processing, and administrative tools. The application uses a modern full-stack architecture with React frontend, Express.js backend, and PostgreSQL database.

## System Architecture

### Frontend Architecture
- **Framework**: React 18 with TypeScript
- **Routing**: Wouter for client-side routing
- **State Management**: TanStack React Query for server state management
- **UI Components**: shadcn/ui component library with Radix UI primitives
- **Styling**: Tailwind CSS with custom design system
- **Build Tool**: Vite for development and production builds

### Backend Architecture
- **Framework**: Express.js with TypeScript
- **Authentication**: Replit Auth with OpenID Connect integration
- **Session Management**: Express sessions with PostgreSQL store
- **Database ORM**: Drizzle ORM for type-safe database operations
- **File Structure**: Modular service-based architecture

### Database Design
- **Primary Database**: PostgreSQL via Neon Database
- **Schema Management**: Drizzle Kit for migrations and schema management
- **Key Tables**:
  - `users` - User profiles and authentication
  - `events` - Event information and metadata
  - `bookings` - Ticket purchases and reservations
  - `cartItems` - Shopping cart functionality
  - `sessions` - Session storage for authentication

## Key Components

### Authentication System
- Replit Auth integration with OpenID Connect
- Session-based authentication with PostgreSQL session store
- Role-based access control (user/admin)
- Automatic session management and renewal

### Event Management
- Event creation, editing, and deletion
- Category-based organization (music, business, technology, arts, sports, food)
- Geographic filtering by Cameroon cities and regions
- Search and filtering capabilities
- Image upload and management

### Booking System
- Shopping cart functionality with persistent storage
- Multi-step checkout process with form validation
- QR code generation for ticket verification
- Email confirmation with PDF ticket attachments
- Booking status tracking and management

### Payment Integration
- Stripe integration for payment processing
- Support for multiple payment methods
- Transaction tracking and management
- Revenue analytics and reporting

### Admin Dashboard
- Event management interface
- Booking oversight and status updates
- Analytics dashboard with charts and metrics
- User management capabilities

## Data Flow

1. **User Authentication**: Users authenticate via Replit Auth, creating/updating user records
2. **Event Discovery**: Events are fetched with filtering, searching, and pagination
3. **Cart Management**: Items added to cart are stored in database with user association
4. **Checkout Process**: Form validation, payment processing, and booking creation
5. **Ticket Generation**: QR codes and PDF tickets generated and emailed to users
6. **Admin Management**: Real-time updates to events, bookings, and analytics

## External Dependencies

### Authentication & Security
- Replit Auth for user authentication
- OpenID Connect for identity management
- Express sessions for session management

### Database & Storage
- Neon Database (PostgreSQL) for primary data storage
- Drizzle ORM for database operations
- Connect-pg-simple for session storage

### Payment Processing
- Stripe for payment processing
- Stripe React components for checkout UI

### Communication & Notifications
- Nodemailer for email delivery
- SMTP integration for email sending
- QR code generation for tickets
- PDF generation for ticket documents

### UI & Styling
- Radix UI for accessible component primitives
- Tailwind CSS for styling
- Lucide React for icons
- React Hook Form for form management

## Deployment Strategy

### Development Environment
- Replit-based development with hot reloading
- Vite dev server for frontend development
- TSX for TypeScript execution in development
- Environment variable management for configuration

### Production Deployment
- Automated build process with Vite and esbuild
- Static file serving for frontend assets
- Express server for API and authentication
- PostgreSQL database with connection pooling
- Environment-based configuration management

### Build Process
1. Frontend: Vite builds React app to static assets
2. Backend: esbuild bundles Express server with dependencies
3. Database: Drizzle migrations ensure schema consistency
4. Deployment: Replit autoscale deployment with health checks

## Changelog
- June 16, 2025. XAMPP version enhanced with complete admin panel functionality
  - Added comprehensive checkout process with payment method selection
  - Implemented booking history dashboard with QR code generation and ticket downloads
  - Created full admin panel with event management, booking oversight, and analytics
  - Added 10 authentic Cameroon events as sample data
  - Implemented CSV export and detailed reporting features
- June 15, 2025. Initial setup

## User Preferences

Preferred communication style: Simple, everyday language.