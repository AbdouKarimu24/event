import jsPDF from 'jspdf';
import { Booking, Event } from '@shared/schema';

export class PDFService {
  static async generateTicketPDF(
    booking: Booking & { event: Event },
    qrCodeDataURL: string
  ): Promise<Buffer> {
    const doc = new jsPDF();
    
    // Colors
    const primaryColor = [102, 126, 234]; // Blue
    const textColor = [51, 51, 51]; // Dark gray
    
    // Header
    doc.setFillColor(...primaryColor);
    doc.rect(0, 0, 210, 40, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(24);
    doc.setFont('helvetica', 'bold');
    doc.text('EventZon', 20, 25);
    
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.text('Billet d\'Événement - Cameroun', 20, 32);
    
    // Event Details
    doc.setTextColor(...textColor);
    doc.setFontSize(18);
    doc.setFont('helvetica', 'bold');
    doc.text(booking.event.title, 20, 55);
    
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    
    // Event info
    let yPos = 70;
    doc.text('📍 Lieu:', 20, yPos);
    doc.text(booking.event.venue, 50, yPos);
    
    yPos += 8;
    doc.text('📧 Adresse:', 20, yPos);
    doc.text(`${booking.event.address}, ${booking.event.city}`, 50, yPos);
    
    yPos += 8;
    doc.text('🌍 Région:', 20, yPos);
    doc.text(`${booking.event.region || 'Cameroun'}`, 50, yPos);
    
    yPos += 8;
    doc.text('📅 Date:', 20, yPos);
    doc.text(new Date(booking.event.eventDate).toLocaleDateString('fr-FR'), 50, yPos);
    
    yPos += 8;
    doc.text('🕐 Heure:', 20, yPos);
    doc.text(booking.event.startTime, 50, yPos);
    
    // Booking details
    yPos += 15;
    doc.setFont('helvetica', 'bold');
    doc.text('Détails de la réservation:', 20, yPos);
    
    yPos += 10;
    doc.setFont('helvetica', 'normal');
    doc.text('Nom:', 20, yPos);
    doc.text(booking.attendeeName, 50, yPos);
    
    yPos += 8;
    doc.text('Email:', 20, yPos);
    doc.text(booking.attendeeEmail, 50, yPos);
    
    yPos += 8;
    doc.text('Téléphone:', 20, yPos);
    doc.text(booking.attendeePhone || 'N/A', 50, yPos);
    
    yPos += 8;
    doc.text('Billets:', 20, yPos);
    doc.text(booking.quantity.toString(), 50, yPos);
    
    yPos += 8;
    doc.text('Total:', 20, yPos);
    doc.setFont('helvetica', 'bold');
    doc.text(`${booking.totalAmount} ${booking.currency}`, 50, yPos);
    
    yPos += 8;
    doc.setFont('helvetica', 'normal');
    doc.text('Réf:', 20, yPos);
    doc.text(booking.bookingReference || 'N/A', 50, yPos);
    
    // QR Code
    if (qrCodeDataURL) {
      doc.addImage(qrCodeDataURL, 'PNG', 130, 70, 60, 60);
      doc.setFontSize(10);
      doc.text('Scannez pour vérifier', 135, 140);
    }
    
    // Instructions
    yPos += 25;
    doc.setFillColor(243, 244, 246);
    doc.rect(15, yPos - 5, 180, 35, 'F');
    
    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.text('Instructions importantes:', 20, yPos + 5);
    
    doc.setFont('helvetica', 'normal');
    doc.text('• Arrivez 15 minutes avant le début', 20, yPos + 12);
    doc.text('• Présentez ce billet et une pièce d\'identité', 20, yPos + 18);
    doc.text('• Ce billet est personnel et non transférable', 20, yPos + 24);
    
    // Footer
    doc.setFontSize(8);
    doc.setTextColor(128, 128, 128);
    doc.text('EventZon - Plateforme de réservation d\'événements au Cameroun', 20, 280);
    doc.text('support@eventzon.cm | +237 6XX XXX XXX', 20, 285);
    
    return Buffer.from(doc.output('arraybuffer'));
  }
}