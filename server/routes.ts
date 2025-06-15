import type { Express } from "express";
import { createServer, type Server } from "http";
import { storage } from "./storage";
import { setupAuth, isAuthenticated } from "./replitAuth";
import { QRCodeService } from "./services/qrCodeService";
import { EmailService } from "./services/emailService";
import { PDFService } from "./services/pdfService";
import { AnalyticsService } from "./services/analyticsService";
import { insertBookingSchema, insertEventSchema, insertCartItemSchema } from "@shared/schema";
import { z } from "zod";
import { db } from "./db";

export async function registerRoutes(app: Express): Promise<Server> {
  // Auth middleware
  await setupAuth(app);

  // Auth routes
  app.get('/api/auth/user', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      res.json(user);
    } catch (error) {
      console.error("Error fetching user:", error);
      res.status(500).json({ message: "Failed to fetch user" });
    }
  });

  // Event routes
  app.get('/api/events', async (req, res) => {
    try {
      const { search, category, city, date, sortBy } = req.query;
      const events = await storage.getEvents({
        search: search as string,
        category: category as string,
        city: city as string,
        date: date as string,
        sortBy: sortBy as "date" | "price" | "popularity",
      });
      res.json(events);
    } catch (error) {
      console.error("Error fetching events:", error);
      res.status(500).json({ message: "Failed to fetch events" });
    }
  });

  app.get('/api/events/:id', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id);
      const event = await storage.getEvent(eventId);
      if (!event) {
        return res.status(404).json({ message: "Event not found" });
      }
      res.json(event);
    } catch (error) {
      console.error("Error fetching event:", error);
      res.status(500).json({ message: "Failed to fetch event" });
    }
  });

  app.post('/api/events', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const eventData = insertEventSchema.parse({ ...req.body, organizerId: userId });
      const event = await storage.createEvent(eventData);
      res.status(201).json(event);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ message: "Invalid event data", errors: error.errors });
      }
      console.error("Error creating event:", error);
      res.status(500).json({ message: "Failed to create event" });
    }
  });

  app.put('/api/events/:id', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      const eventId = parseInt(req.params.id);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const eventData = insertEventSchema.partial().parse(req.body);
      const event = await storage.updateEvent(eventId, eventData);
      res.json(event);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ message: "Invalid event data", errors: error.errors });
      }
      console.error("Error updating event:", error);
      res.status(500).json({ message: "Failed to update event" });
    }
  });

  app.delete('/api/events/:id', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      const eventId = parseInt(req.params.id);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      await storage.deleteEvent(eventId);
      res.status(204).send();
    } catch (error) {
      console.error("Error deleting event:", error);
      res.status(500).json({ message: "Failed to delete event" });
    }
  });

  // Booking routes
  app.get('/api/bookings', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const bookings = await storage.getUserBookings(userId);
      res.json(bookings);
    } catch (error) {
      console.error("Error fetching bookings:", error);
      res.status(500).json({ message: "Failed to fetch bookings" });
    }
  });

  app.post('/api/bookings', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const bookingData = insertBookingSchema.parse({ ...req.body, userId });
      
      // Check if event exists and has available tickets
      const event = await storage.getEvent(bookingData.eventId);
      if (!event) {
        return res.status(404).json({ message: "Event not found" });
      }
      
      if (event.availableTickets && event.availableTickets < bookingData.quantity) {
        return res.status(400).json({ message: "Not enough tickets available" });
      }

      const booking = await storage.createBooking(bookingData);
      const bookingWithEvent = { ...booking, event };

      // Generate QR code for the ticket
      try {
        const qrCode = await QRCodeService.generateQRCode({
          bookingId: booking.id,
          ticketNumber: booking.ticketNumber || `TK${booking.id}`,
          eventTitle: event.title,
          venue: event.venue,
          eventDate: event.eventDate,
          attendeeName: booking.attendeeName,
          quantity: booking.quantity
        });

        // Update booking with QR code
        await storage.updateBookingQRCode(booking.id, qrCode);

        // Send confirmation email
        await EmailService.sendBookingConfirmation(bookingWithEvent, qrCode);
      } catch (emailError) {
        console.error("Error with QR/email:", emailError);
      }
      
      // Update available tickets
      if (event.availableTickets) {
        await storage.updateEvent(event.id, {
          availableTickets: event.availableTickets - bookingData.quantity
        });
      }

      // Clear cart items for this event
      await storage.clearUserCart(userId);

      res.status(201).json(booking);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ message: "Invalid booking data", errors: error.errors });
      }
      console.error("Error creating booking:", error);
      res.status(500).json({ message: "Failed to create booking" });
    }
  });

  // Admin booking routes
  app.get('/api/admin/bookings', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const bookings = await storage.getAllBookings();
      res.json(bookings);
    } catch (error) {
      console.error("Error fetching all bookings:", error);
      res.status(500).json({ message: "Failed to fetch bookings" });
    }
  });

  app.get('/api/admin/events/:id/bookings', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const eventId = parseInt(req.params.id);
      const bookings = await storage.getEventBookings(eventId);
      res.json(bookings);
    } catch (error) {
      console.error("Error fetching event bookings:", error);
      res.status(500).json({ message: "Failed to fetch event bookings" });
    }
  });

  // Cart routes
  app.get('/api/cart', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const cartItems = await storage.getUserCartItems(userId);
      res.json(cartItems);
    } catch (error) {
      console.error("Error fetching cart:", error);
      res.status(500).json({ message: "Failed to fetch cart" });
    }
  });

  app.post('/api/cart', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const cartItemData = insertCartItemSchema.parse({ ...req.body, userId });
      const cartItem = await storage.addToCart(cartItemData);
      res.status(201).json(cartItem);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ message: "Invalid cart item data", errors: error.errors });
      }
      console.error("Error adding to cart:", error);
      res.status(500).json({ message: "Failed to add to cart" });
    }
  });

  app.put('/api/cart/:id', isAuthenticated, async (req: any, res) => {
    try {
      const cartItemId = parseInt(req.params.id);
      const { quantity } = req.body;
      
      if (!quantity || quantity < 1) {
        return res.status(400).json({ message: "Invalid quantity" });
      }

      const cartItem = await storage.updateCartItem(cartItemId, quantity);
      res.json(cartItem);
    } catch (error) {
      console.error("Error updating cart item:", error);
      res.status(500).json({ message: "Failed to update cart item" });
    }
  });

  app.delete('/api/cart/:id', isAuthenticated, async (req: any, res) => {
    try {
      const cartItemId = parseInt(req.params.id);
      await storage.removeFromCart(cartItemId);
      res.status(204).send();
    } catch (error) {
      console.error("Error removing from cart:", error);
      res.status(500).json({ message: "Failed to remove from cart" });
    }
  });

  app.delete('/api/cart', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      await storage.clearUserCart(userId);
      res.status(204).send();
    } catch (error) {
      console.error("Error clearing cart:", error);
      res.status(500).json({ message: "Failed to clear cart" });
    }
  });

  // Analytics routes
  app.get('/api/analytics', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const { startDate, endDate } = req.query;
      const analytics = await AnalyticsService.getEventAnalytics(
        startDate ? new Date(startDate) : undefined,
        endDate ? new Date(endDate) : undefined
      );
      
      res.json(analytics);
    } catch (error) {
      console.error("Error fetching analytics:", error);
      res.status(500).json({ message: "Failed to fetch analytics" });
    }
  });

  // Cameroon regions and cities data
  app.get('/api/cameroon/regions', async (req, res) => {
    try {
      const regions = await AnalyticsService.getCameroonRegionsData();
      res.json(regions);
    } catch (error) {
      console.error("Error fetching regions:", error);
      res.status(500).json({ message: "Failed to fetch regions" });
    }
  });

  app.get('/api/cameroon/cities', async (req, res) => {
    try {
      const cities = await AnalyticsService.getCameroonCitiesData();
      res.json(cities);
    } catch (error) {
      console.error("Error fetching cities:", error);
      res.status(500).json({ message: "Failed to fetch cities" });
    }
  });

  // PDF ticket download
  app.get('/api/bookings/:id/pdf', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const bookingId = parseInt(req.params.id);
      const bookings = await storage.getUserBookings(userId);
      const booking = bookings.find(b => b.id === bookingId);
      
      if (!booking) {
        return res.status(404).json({ message: "Booking not found" });
      }

      const qrCode = booking.qrCode || await QRCodeService.generateQRCode({
        bookingId: booking.id,
        ticketNumber: booking.ticketNumber || `TK${booking.id}`,
        eventTitle: booking.event.title,
        venue: booking.event.venue,
        eventDate: booking.event.eventDate,
        attendeeName: booking.attendeeName,
        quantity: booking.quantity
      });

      const pdfBuffer = await PDFService.generateTicketPDF(booking, qrCode);
      
      res.setHeader('Content-Type', 'application/pdf');
      res.setHeader('Content-Disposition', `attachment; filename="ticket-${booking.ticketNumber || booking.id}.pdf"`);
      res.send(pdfBuffer);
    } catch (error) {
      console.error("Error generating PDF:", error);
      res.status(500).json({ message: "Failed to generate PDF ticket" });
    }
  });

  // QR code verification
  app.get('/api/verify-ticket/:ticketNumber', async (req, res) => {
    try {
      const { ticketNumber } = req.params;
      const verification = await QRCodeService.verifyTicket(ticketNumber);
      res.json(verification);
    } catch (error) {
      console.error("Error verifying ticket:", error);
      res.status(500).json({ message: "Failed to verify ticket" });
    }
  });

  // Database administration routes
  app.get('/api/admin/database/tables', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const result = await db.execute(`
        SELECT 
          tablename as table_name,
          COALESCE(n_tup_ins + n_tup_upd + n_tup_del, 0) as row_count,
          pg_size_pretty(pg_total_relation_size('public.'||tablename)) as table_size
        FROM pg_tables 
        LEFT JOIN pg_stat_user_tables ON pg_tables.tablename = pg_stat_user_tables.relname
        WHERE schemaname = 'public'
        ORDER BY tablename
      `);
      
      res.json(result.rows);
    } catch (error) {
      console.error("Error fetching tables:", error);
      res.status(500).json({ message: "Failed to fetch tables" });
    }
  });

  app.get('/api/admin/database/table/:tableName', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const tableName = req.params.tableName;
      
      // Validate table name to prevent SQL injection
      const validTables = ['users', 'events', 'bookings', 'cart_items', 'sessions'];
      if (!validTables.includes(tableName)) {
        return res.status(400).json({ message: "Invalid table name" });
      }

      const result = await db.execute(`SELECT * FROM ${tableName} LIMIT 50`);
      
      const columns = result.fields?.map(field => field.name) || [];
      const rows = result.rows || [];
      
      res.json({ columns, rows });
    } catch (error) {
      console.error("Error fetching table data:", error);
      res.status(500).json({ message: "Failed to fetch table data" });
    }
  });

  app.post('/api/admin/database/execute', isAuthenticated, async (req: any, res) => {
    try {
      const userId = req.user.claims.sub;
      const user = await storage.getUser(userId);
      
      if (user?.role !== 'admin') {
        return res.status(403).json({ message: "Access denied. Admin role required." });
      }

      const { query } = req.body;
      
      if (!query || typeof query !== 'string') {
        return res.status(400).json({ message: "Query is required" });
      }

      // Basic safety checks
      const dangerousKeywords = ['DROP', 'TRUNCATE', 'ALTER', 'CREATE', 'GRANT', 'REVOKE'];
      const upperQuery = query.toUpperCase();
      
      for (const keyword of dangerousKeywords) {
        if (upperQuery.includes(keyword)) {
          return res.status(400).json({ 
            message: `Dangerous operation detected: ${keyword}. Only SELECT, INSERT, UPDATE, DELETE queries are allowed.` 
          });
        }
      }

      const startTime = Date.now();
      const result = await db.execute(query);
      const executionTime = Date.now() - startTime;
      
      const columns = result.fields?.map(field => field.name) || [];
      const rows = result.rows || [];
      const rowCount = result.rowCount || 0;
      
      res.json({ columns, rows, rowCount, executionTime });
    } catch (error) {
      console.error("Error executing query:", error);
      res.status(500).json({ message: error.message || "Failed to execute query" });
    }
  });

  const httpServer = createServer(app);
  return httpServer;
}
