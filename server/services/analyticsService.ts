import { db } from '../db';
import { events, bookings, users } from '@shared/schema';
import { eq, gte, lte, sql, desc, asc } from 'drizzle-orm';

export interface EventAnalytics {
  totalEvents: number;
  totalBookings: number;
  totalRevenue: number;
  averageTicketPrice: number;
  popularCities: Array<{ city: string; count: number }>;
  popularRegions: Array<{ region: string; count: number }>;
  popularCategories: Array<{ category: string; count: number }>;
  revenueByMonth: Array<{ month: string; revenue: number }>;
  bookingsByMonth: Array<{ month: string; bookings: number }>;
  topEvents: Array<{ title: string; bookings: number; revenue: number }>;
}

export class AnalyticsService {
  static async getEventAnalytics(startDate?: Date, endDate?: Date): Promise<EventAnalytics> {
    const dateFilter = startDate && endDate 
      ? [gte(bookings.createdAt, startDate), lte(bookings.createdAt, endDate)]
      : [];

    // Total events
    const totalEventsResult = await db
      .select({ count: sql<number>`count(*)` })
      .from(events);
    const totalEvents = totalEventsResult[0]?.count || 0;

    // Total bookings and revenue
    const bookingStatsQuery = db
      .select({
        totalBookings: sql<number>`count(*)`,
        totalRevenue: sql<number>`sum(${bookings.totalAmount})`,
        averageTicketPrice: sql<number>`avg(${bookings.totalAmount} / ${bookings.quantity})`
      })
      .from(bookings);

    if (dateFilter.length > 0) {
      bookingStatsQuery.where(sql`${dateFilter.join(' AND ')}`);
    }

    const bookingStats = await bookingStatsQuery;
    const stats = bookingStats[0] || { totalBookings: 0, totalRevenue: 0, averageTicketPrice: 0 };

    // Popular cities (Cameroon cities)
    const popularCities = await db
      .select({
        city: events.city,
        count: sql<number>`count(*)`
      })
      .from(events)
      .innerJoin(bookings, eq(events.id, bookings.eventId))
      .where(dateFilter.length > 0 ? sql`${dateFilter.join(' AND ')}` : undefined)
      .groupBy(events.city)
      .orderBy(desc(sql`count(*)`))
      .limit(10);

    // Popular regions (Cameroon regions)
    const popularRegions = await db
      .select({
        region: events.region,
        count: sql<number>`count(*)`
      })
      .from(events)
      .innerJoin(bookings, eq(events.id, bookings.eventId))
      .where(dateFilter.length > 0 ? sql`${dateFilter.join(' AND ')}` : undefined)
      .groupBy(events.region)
      .orderBy(desc(sql`count(*)`))
      .limit(10);

    // Popular categories
    const popularCategories = await db
      .select({
        category: events.category,
        count: sql<number>`count(*)`
      })
      .from(events)
      .innerJoin(bookings, eq(events.id, bookings.eventId))
      .where(dateFilter.length > 0 ? sql`${dateFilter.join(' AND ')}` : undefined)
      .groupBy(events.category)
      .orderBy(desc(sql`count(*)`))
      .limit(10);

    // Revenue by month
    const revenueByMonth = await db
      .select({
        month: sql<string>`to_char(${bookings.createdAt}, 'YYYY-MM')`,
        revenue: sql<number>`sum(${bookings.totalAmount})`
      })
      .from(bookings)
      .where(dateFilter.length > 0 ? sql`${dateFilter.join(' AND ')}` : undefined)
      .groupBy(sql`to_char(${bookings.createdAt}, 'YYYY-MM')`)
      .orderBy(asc(sql`to_char(${bookings.createdAt}, 'YYYY-MM')`));

    // Bookings by month
    const bookingsByMonth = await db
      .select({
        month: sql<string>`to_char(${bookings.createdAt}, 'YYYY-MM')`,
        bookings: sql<number>`count(*)`
      })
      .from(bookings)
      .where(dateFilter.length > 0 ? sql`${dateFilter.join(' AND ')}` : undefined)
      .groupBy(sql`to_char(${bookings.createdAt}, 'YYYY-MM')`)
      .orderBy(asc(sql`to_char(${bookings.createdAt}, 'YYYY-MM')`));

    // Top events
    const topEvents = await db
      .select({
        title: events.title,
        bookings: sql<number>`count(${bookings.id})`,
        revenue: sql<number>`sum(${bookings.totalAmount})`
      })
      .from(events)
      .innerJoin(bookings, eq(events.id, bookings.eventId))
      .where(dateFilter.length > 0 ? sql`${dateFilter.join(' AND ')}` : undefined)
      .groupBy(events.id, events.title)
      .orderBy(desc(sql`sum(${bookings.totalAmount})`))
      .limit(10);

    return {
      totalEvents,
      totalBookings: stats.totalBookings,
      totalRevenue: stats.totalRevenue,
      averageTicketPrice: stats.averageTicketPrice,
      popularCities: popularCities.map(c => ({ city: c.city || 'Non spécifié', count: c.count })),
      popularRegions: popularRegions.map(r => ({ region: r.region || 'Non spécifié', count: r.count })),
      popularCategories: popularCategories.map(c => ({ category: c.category, count: c.count })),
      revenueByMonth: revenueByMonth.map(r => ({ month: r.month, revenue: r.revenue })),
      bookingsByMonth: bookingsByMonth.map(b => ({ month: b.month, bookings: b.bookings })),
      topEvents: topEvents.map(e => ({ 
        title: e.title, 
        bookings: e.bookings, 
        revenue: e.revenue 
      }))
    };
  }

  static async getCameroonRegionsData() {
    return [
      { name: 'Adamaoua', capital: 'Ngaoundéré' },
      { name: 'Centre', capital: 'Yaoundé' },
      { name: 'Est', capital: 'Bertoua' },
      { name: 'Extrême-Nord', capital: 'Maroua' },
      { name: 'Littoral', capital: 'Douala' },
      { name: 'Nord', capital: 'Garoua' },
      { name: 'Nord-Ouest', capital: 'Bamenda' },
      { name: 'Ouest', capital: 'Bafoussam' },
      { name: 'Sud', capital: 'Ebolowa' },
      { name: 'Sud-Ouest', capital: 'Buea' }
    ];
  }

  static async getCameroonCitiesData() {
    return [
      // Major cities by region
      { name: 'Yaoundé', region: 'Centre' },
      { name: 'Douala', region: 'Littoral' },
      { name: 'Garoua', region: 'Nord' },
      { name: 'Maroua', region: 'Extrême-Nord' },
      { name: 'Bamenda', region: 'Nord-Ouest' },
      { name: 'Bafoussam', region: 'Ouest' },
      { name: 'Ngaoundéré', region: 'Adamaoua' },
      { name: 'Bertoua', region: 'Est' },
      { name: 'Ebolowa', region: 'Sud' },
      { name: 'Buea', region: 'Sud-Ouest' },
      { name: 'Limbé', region: 'Sud-Ouest' },
      { name: 'Kumba', region: 'Sud-Ouest' },
      { name: 'Edéa', region: 'Littoral' },
      { name: 'Kribi', region: 'Sud' },
      { name: 'Dschang', region: 'Ouest' },
      { name: 'Mbouda', region: 'Ouest' },
      { name: 'Foumban', region: 'Ouest' },
      { name: 'Bafang', region: 'Ouest' },
      { name: 'Mbalmayo', region: 'Centre' },
      { name: 'Sangmélima', region: 'Sud' }
    ];
  }
}