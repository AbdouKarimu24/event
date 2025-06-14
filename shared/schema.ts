import {
  pgTable,
  text,
  varchar,
  timestamp,
  jsonb,
  index,
  serial,
  integer,
  decimal,
  boolean,
  date,
  time,
} from "drizzle-orm/pg-core";
import { relations } from "drizzle-orm";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

// Session storage table (required for Replit Auth)
export const sessions = pgTable(
  "sessions",
  {
    sid: varchar("sid").primaryKey(),
    sess: jsonb("sess").notNull(),
    expire: timestamp("expire").notNull(),
  },
  (table) => [index("IDX_session_expire").on(table.expire)],
);

// User storage table (required for Replit Auth)
export const users = pgTable("users", {
  id: varchar("id").primaryKey().notNull(),
  email: varchar("email").unique(),
  firstName: varchar("first_name"),
  lastName: varchar("last_name"),
  profileImageUrl: varchar("profile_image_url"),
  role: varchar("role").default("user"), // user, admin
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
});

// Events table
export const events = pgTable("events", {
  id: serial("id").primaryKey(),
  title: varchar("title", { length: 255 }).notNull(),
  description: text("description"),
  category: varchar("category", { length: 100 }).notNull(),
  organizerId: varchar("organizer_id").references(() => users.id),
  venue: varchar("venue", { length: 255 }).notNull(),
  address: varchar("address", { length: 500 }),
  city: varchar("city", { length: 100 }),
  state: varchar("state", { length: 100 }),
  country: varchar("country", { length: 100 }),
  eventDate: date("event_date").notNull(),
  startTime: time("start_time").notNull(),
  endTime: time("end_time"),
  imageUrl: varchar("image_url"),
  price: decimal("price", { precision: 10, scale: 2 }).notNull(),
  maxAttendees: integer("max_attendees"),
  availableTickets: integer("available_tickets"),
  status: varchar("status").default("active"), // active, cancelled, sold_out
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
});

// Bookings table
export const bookings = pgTable("bookings", {
  id: serial("id").primaryKey(),
  userId: varchar("user_id").references(() => users.id).notNull(),
  eventId: integer("event_id").references(() => events.id).notNull(),
  quantity: integer("quantity").notNull(),
  totalAmount: decimal("total_amount", { precision: 10, scale: 2 }).notNull(),
  attendeeName: varchar("attendee_name", { length: 255 }).notNull(),
  attendeeEmail: varchar("attendee_email", { length: 255 }).notNull(),
  attendeePhone: varchar("attendee_phone", { length: 20 }),
  status: varchar("status").default("confirmed"), // confirmed, cancelled
  bookingReference: varchar("booking_reference").unique(),
  createdAt: timestamp("created_at").defaultNow(),
});

// Cart items table (for managing cart state)
export const cartItems = pgTable("cart_items", {
  id: serial("id").primaryKey(),
  userId: varchar("user_id").references(() => users.id).notNull(),
  eventId: integer("event_id").references(() => events.id).notNull(),
  quantity: integer("quantity").notNull(),
  createdAt: timestamp("created_at").defaultNow(),
});

// Relations
export const usersRelations = relations(users, ({ many }) => ({
  events: many(events),
  bookings: many(bookings),
  cartItems: many(cartItems),
}));

export const eventsRelations = relations(events, ({ one, many }) => ({
  organizer: one(users, {
    fields: [events.organizerId],
    references: [users.id],
  }),
  bookings: many(bookings),
  cartItems: many(cartItems),
}));

export const bookingsRelations = relations(bookings, ({ one }) => ({
  user: one(users, {
    fields: [bookings.userId],
    references: [users.id],
  }),
  event: one(events, {
    fields: [bookings.eventId],
    references: [events.id],
  }),
}));

export const cartItemsRelations = relations(cartItems, ({ one }) => ({
  user: one(users, {
    fields: [cartItems.userId],
    references: [users.id],
  }),
  event: one(events, {
    fields: [cartItems.eventId],
    references: [events.id],
  }),
}));

// Insert schemas
export const insertUserSchema = createInsertSchema(users).pick({
  email: true,
  firstName: true,
  lastName: true,
  profileImageUrl: true,
});

export const insertEventSchema = createInsertSchema(events).omit({
  id: true,
  createdAt: true,
  updatedAt: true,
});

export const insertBookingSchema = createInsertSchema(bookings).omit({
  id: true,
  createdAt: true,
  bookingReference: true,
});

export const insertCartItemSchema = createInsertSchema(cartItems).omit({
  id: true,
  createdAt: true,
});

// Types
export type UpsertUser = z.infer<typeof insertUserSchema> & { id: string };
export type User = typeof users.$inferSelect;
export type Event = typeof events.$inferSelect;
export type InsertEvent = z.infer<typeof insertEventSchema>;
export type Booking = typeof bookings.$inferSelect;
export type InsertBooking = z.infer<typeof insertBookingSchema>;
export type CartItem = typeof cartItems.$inferSelect;
export type InsertCartItem = z.infer<typeof insertCartItemSchema>;

// Extended types for API responses
export type EventWithOrganizer = Event & {
  organizer: User | null;
};

export type BookingWithEvent = Booking & {
  event: Event;
};

export type CartItemWithEvent = CartItem & {
  event: Event;
};
