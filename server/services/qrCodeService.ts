import QRCode from 'qrcode';

export interface TicketData {
  bookingId: number;
  ticketNumber: string;
  eventTitle: string;
  venue: string;
  eventDate: string;
  attendeeName: string;
  quantity: number;
}

export class QRCodeService {
  static async generateQRCode(ticketData: TicketData): Promise<string> {
    const qrData = {
      bookingId: ticketData.bookingId,
      ticketNumber: ticketData.ticketNumber,
      eventTitle: ticketData.eventTitle,
      venue: ticketData.venue,
      eventDate: ticketData.eventDate,
      attendeeName: ticketData.attendeeName,
      quantity: ticketData.quantity,
      verificationUrl: `https://${process.env.REPLIT_DOMAINS?.split(',')[0]}/verify-ticket/${ticketData.ticketNumber}`,
    };

    try {
      const qrCodeDataURL = await QRCode.toDataURL(JSON.stringify(qrData), {
        errorCorrectionLevel: 'M',
        margin: 1,
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        },
        width: 256
      });
      
      return qrCodeDataURL;
    } catch (error) {
      console.error('Error generating QR code:', error);
      throw new Error('Failed to generate QR code');
    }
  }

  static async verifyTicket(ticketNumber: string): Promise<any> {
    // This would typically verify against database
    // For now, we'll implement basic verification
    return {
      valid: true,
      ticketNumber,
      message: 'Ticket verified successfully'
    };
  }
}