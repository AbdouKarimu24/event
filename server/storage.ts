import {
  users,
  events,
  bookings,
  cartItems,
  type User,
  type UpsertUser,
  type Event,
  type InsertEvent,
  type EventWithOrganizer,
  type Booking,
  type InsertBooking,
  type BookingWithEvent,
  type CartItem,
  type InsertCartItem,
  type CartItemWithEvent,
} from "@shared/schema";
import { db } from "./db";
import { eq, and, desc, asc, like, or } from "drizzle-orm";

export interface IStorage {
  // User operations (required for Replit Auth)
  getUser(id: string): Promise<User | undefined>;
  upsertUser(user: UpsertUser): Promise<User>;

  // Event operations
  getEvents(filters?: {
    search?: string;
    category?: string;
    city?: string;
    date?: string;
    sortBy?: "date" | "price" | "popularity";
  }): Promise<EventWithOrganizer[]>;
  getEvent(id: number): Promise<EventWithOrganizer | undefined>;
  createEvent(event: InsertEvent): Promise<Event>;
  updateEvent(id: number, event: Partial<InsertEvent>): Promise<Event>;
  deleteEvent(id: number): Promise<void>;

  // Booking operations
  createBooking(booking: InsertBooking): Promise<Booking>;
  getUserBookings(userId: string): Promise<BookingWithEvent[]>;
  getEventBookings(eventId: number): Promise<BookingWithEvent[]>;
  getAllBookings(): Promise<BookingWithEvent[]>;
  updateBookingStatus(id: number, status: string): Promise<Booking>;
  updateBookingQRCode(id: number, qrCode: string): Promise<Booking>;

  // Cart operations
  getUserCartItems(userId: string): Promise<CartItemWithEvent[]>;
  addToCart(cartItem: InsertCartItem): Promise<CartItem>;
  updateCartItem(id: number, quantity: number): Promise<CartItem>;
  removeFromCart(id: number): Promise<void>;
  clearUserCart(userId: string): Promise<void>;
}

export class DatabaseStorage implements IStorage {
  // User operations
  async getUser(id: string): Promise<User | undefined> {
    const [user] = await db.select().from(users).where(eq(users.id, id));
    return user;
  }

  async upsertUser(userData: UpsertUser): Promise<User> {
    const [user] = await db
      .insert(users)
      .values(userData)
      .onConflictDoUpdate({
        target: users.id,
        set: {
          ...userData,
          updatedAt: new Date(),
        },
      })
      .returning();
    return user;
  }

  // Event operations
  async getEvents(filters?: {
    search?: string;
    category?: string;
    city?: string;
    date?: string;
    sortBy?: "date" | "price" | "popularity";
  }): Promise<EventWithOrganizer[]> {
    let query = db
      .select({
        id: events.id,
        title: events.title,
        description: events.description,
        category: events.category,
        organizerId: events.organizerId,
        venue: events.venue,
        address: events.address,
        city: events.city,
        state: events.state,
        country: events.country,
        eventDate: events.eventDate,
        startTime: events.startTime,
        endTime: events.endTime,
        imageUrl: events.imageUrl,
        price: events.price,
        maxAttendees: events.maxAttendees,
        availableTickets: events.availableTickets,
        status: events.status,
        createdAt: events.createdAt,
        updatedAt: events.updatedAt,
        organizer: {
          id: users.id,
          email: users.email,
          firstName: users.firstName,
          lastName: users.lastName,
          profileImageUrl: users.profileImageUrl,
          role: users.role,
          createdAt: users.createdAt,
          updatedAt: users.updatedAt,
        },
      })
      .from(events)
      .leftJoin(users, eq(events.organizerId, users.id));

    // Apply filters
    const conditions = [];
    
    if (filters?.search) {
      conditions.push(
        or(
          like(events.title, `%${filters.search}%`),
          like(events.description, `%${filters.search}%`),
          like(events.venue, `%${filters.search}%`)
        )
      );
    }
    
    if (filters?.category) {
      conditions.push(eq(events.category, filters.category));
    }
    
    if (filters?.city) {
      conditions.push(eq(events.city, filters.city));
    }
    
    if (filters?.date) {
      conditions.push(eq(events.eventDate, filters.date));
    }

    conditions.push(eq(events.status, "active"));

    if (conditions.length > 0) {
      query = query.where(and(...conditions));
    }

    // Apply sorting
    if (filters?.sortBy === "price") {
      query = query.orderBy(asc(events.price));
    } else if (filters?.sortBy === "date") {
      query = query.orderBy(asc(events.eventDate));
    } else {
      query = query.orderBy(desc(events.createdAt));
    }

    const result = await query;
    return result.map(row => ({
      ...row,
      organizer: row.organizer.id ? row.organizer : null,
    })) as EventWithOrganizer[];
  }

  async getEvent(id: number): Promise<EventWithOrganizer | undefined> {
    const [result] = await db
      .select({
        id: events.id,
        title: events.title,
        description: events.description,
        category: events.category,
        organizerId: events.organizerId,
        venue: events.venue,
        address: events.address,
        city: events.city,
        state: events.state,
        country: events.country,
        eventDate: events.eventDate,
        startTime: events.startTime,
        endTime: events.endTime,
        imageUrl: events.imageUrl,
        price: events.price,
        maxAttendees: events.maxAttendees,
        availableTickets: events.availableTickets,
        status: events.status,
        createdAt: events.createdAt,
        updatedAt: events.updatedAt,
        organizer: {
          id: users.id,
          email: users.email,
          firstName: users.firstName,
          lastName: users.lastName,
          profileImageUrl: users.profileImageUrl,
          role: users.role,
          createdAt: users.createdAt,
          updatedAt: users.updatedAt,
        },
      })
      .from(events)
      .leftJoin(users, eq(events.organizerId, users.id))
      .where(eq(events.id, id));

    if (!result) return undefined;

    return {
      ...result,
      organizer: result.organizer.id ? result.organizer : null,
    } as EventWithOrganizer;
  }

  async createEvent(event: InsertEvent): Promise<Event> {
    const [newEvent] = await db.insert(events).values(event).returning();
    return newEvent;
  }

  async updateEvent(id: number, event: Partial<InsertEvent>): Promise<Event> {
    const [updatedEvent] = await db
      .update(events)
      .set({ ...event, updatedAt: new Date() })
      .where(eq(events.id, id))
      .returning();
    return updatedEvent;
  }

  async deleteEvent(id: number): Promise<void> {
    await db.delete(events).where(eq(events.id, id));
  }

  // Booking operations
  async createBooking(booking: InsertBooking): Promise<Booking> {
    const bookingReference = `EVT-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    const [newBooking] = await db
      .insert(bookings)
      .values({ ...booking, bookingReference })
      .returning();
    return newBooking;
  }

  async getUserBookings(userId: string): Promise<BookingWithEvent[]> {
    const result = await db
      .select({
        id: bookings.id,
        userId: bookings.userId,
        eventId: bookings.eventId,
        quantity: bookings.quantity,
        totalAmount: bookings.totalAmount,
        attendeeName: bookings.attendeeName,
        attendeeEmail: bookings.attendeeEmail,
        attendeePhone: bookings.attendeePhone,
        status: bookings.status,
        bookingReference: bookings.bookingReference,
        createdAt: bookings.createdAt,
        event: {
          id: events.id,
          title: events.title,
          description: events.description,
          category: events.category,
          organizerId: events.organizerId,
          venue: events.venue,
          address: events.address,
          city: events.city,
          state: events.state,
          country: events.country,
          eventDate: events.eventDate,
          startTime: events.startTime,
          endTime: events.endTime,
          imageUrl: events.imageUrl,
          price: events.price,
          maxAttendees: events.maxAttendees,
          availableTickets: events.availableTickets,
          status: events.status,
          createdAt: events.createdAt,
          updatedAt: events.updatedAt,
        },
      })
      .from(bookings)
      .innerJoin(events, eq(bookings.eventId, events.id))
      .where(eq(bookings.userId, userId))
      .orderBy(desc(bookings.createdAt));

    return result as BookingWithEvent[];
  }

  async getEventBookings(eventId: number): Promise<BookingWithEvent[]> {
    const result = await db
      .select({
        id: bookings.id,
        userId: bookings.userId,
        eventId: bookings.eventId,
        quantity: bookings.quantity,
        totalAmount: bookings.totalAmount,
        attendeeName: bookings.attendeeName,
        attendeeEmail: bookings.attendeeEmail,
        attendeePhone: bookings.attendeePhone,
        status: bookings.status,
        bookingReference: bookings.bookingReference,
        createdAt: bookings.createdAt,
        event: {
          id: events.id,
          title: events.title,
          description: events.description,
          category: events.category,
          organizerId: events.organizerId,
          venue: events.venue,
          address: events.address,
          city: events.city,
          state: events.state,
          country: events.country,
          eventDate: events.eventDate,
          startTime: events.startTime,
          endTime: events.endTime,
          imageUrl: events.imageUrl,
          price: events.price,
          maxAttendees: events.maxAttendees,
          availableTickets: events.availableTickets,
          status: events.status,
          createdAt: events.createdAt,
          updatedAt: events.updatedAt,
        },
      })
      .from(bookings)
      .innerJoin(events, eq(bookings.eventId, events.id))
      .where(eq(bookings.eventId, eventId))
      .orderBy(desc(bookings.createdAt));

    return result as BookingWithEvent[];
  }

  async getAllBookings(): Promise<BookingWithEvent[]> {
    const result = await db
      .select({
        id: bookings.id,
        userId: bookings.userId,
        eventId: bookings.eventId,
        quantity: bookings.quantity,
        totalAmount: bookings.totalAmount,
        attendeeName: bookings.attendeeName,
        attendeeEmail: bookings.attendeeEmail,
        attendeePhone: bookings.attendeePhone,
        status: bookings.status,
        bookingReference: bookings.bookingReference,
        createdAt: bookings.createdAt,
        event: {
          id: events.id,
          title: events.title,
          description: events.description,
          category: events.category,
          organizerId: events.organizerId,
          venue: events.venue,
          address: events.address,
          city: events.city,
          state: events.state,
          country: events.country,
          eventDate: events.eventDate,
          startTime: events.startTime,
          endTime: events.endTime,
          imageUrl: events.imageUrl,
          price: events.price,
          maxAttendees: events.maxAttendees,
          availableTickets: events.availableTickets,
          status: events.status,
          createdAt: events.createdAt,
          updatedAt: events.updatedAt,
        },
      })
      .from(bookings)
      .innerJoin(events, eq(bookings.eventId, events.id))
      .orderBy(desc(bookings.createdAt));

    return result as BookingWithEvent[];
  }

  async updateBookingStatus(id: number, status: string): Promise<Booking> {
    const [updatedBooking] = await db
      .update(bookings)
      .set({ status })
      .where(eq(bookings.id, id))
      .returning();
    return updatedBooking;
  }

  async updateBookingQRCode(id: number, qrCode: string): Promise<Booking> {
    const [updatedBooking] = await db
      .update(bookings)
      .set({ qrCode })
      .where(eq(bookings.id, id))
      .returning();
    return updatedBooking;
  }

  // Cart operations
  async getUserCartItems(userId: string): Promise<CartItemWithEvent[]> {
    const result = await db
      .select({
        id: cartItems.id,
        userId: cartItems.userId,
        eventId: cartItems.eventId,
        quantity: cartItems.quantity,
        createdAt: cartItems.createdAt,
        event: {
          id: events.id,
          title: events.title,
          description: events.description,
          category: events.category,
          organizerId: events.organizerId,
          venue: events.venue,
          address: events.address,
          city: events.city,
          state: events.state,
          country: events.country,
          eventDate: events.eventDate,
          startTime: events.startTime,
          endTime: events.endTime,
          imageUrl: events.imageUrl,
          price: events.price,
          maxAttendees: events.maxAttendees,
          availableTickets: events.availableTickets,
          status: events.status,
          createdAt: events.createdAt,
          updatedAt: events.updatedAt,
        },
      })
      .from(cartItems)
      .innerJoin(events, eq(cartItems.eventId, events.id))
      .where(eq(cartItems.userId, userId))
      .orderBy(desc(cartItems.createdAt));

    return result as CartItemWithEvent[];
  }

  async addToCart(cartItem: InsertCartItem): Promise<CartItem> {
    // Check if item already exists in cart
    const [existingItem] = await db
      .select()
      .from(cartItems)
      .where(
        and(
          eq(cartItems.userId, cartItem.userId),
          eq(cartItems.eventId, cartItem.eventId)
        )
      );

    if (existingItem) {
      // Update quantity
      const [updatedItem] = await db
        .update(cartItems)
        .set({ quantity: existingItem.quantity + cartItem.quantity })
        .where(eq(cartItems.id, existingItem.id))
        .returning();
      return updatedItem;
    } else {
      // Add new item
      const [newItem] = await db.insert(cartItems).values(cartItem).returning();
      return newItem;
    }
  }

  async updateCartItem(id: number, quantity: number): Promise<CartItem> {
    const [updatedItem] = await db
      .update(cartItems)
      .set({ quantity })
      .where(eq(cartItems.id, id))
      .returning();
    return updatedItem;
  }

  async removeFromCart(id: number): Promise<void> {
    await db.delete(cartItems).where(eq(cartItems.id, id));
  }

  async clearUserCart(userId: string): Promise<void> {
    await db.delete(cartItems).where(eq(cartItems.userId, userId));
  }
}

export const storage = new DatabaseStorage();
