import nodemailer from 'nodemailer';
import { Booking, Event, User } from '@shared/schema';

export interface EmailData {
  to: string;
  subject: string;
  html: string;
  attachments?: Array<{
    filename: string;
    content: Buffer | string;
    contentType?: string;
  }>;
}

export class EmailService {
  private static transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST || 'smtp.gmail.com',
    port: parseInt(process.env.SMTP_PORT || '587'),
    secure: false,
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS,
    },
  });

  static async sendBookingConfirmation(
    booking: Booking & { event: Event },
    qrCodeDataURL: string
  ): Promise<void> {
    const emailHtml = `
      <!DOCTYPE html>
      <html lang="fr">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmation de Réservation - EventZon</title>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
          .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
          .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
          .ticket-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
          .qr-code { text-align: center; margin: 20px 0; }
          .footer { text-align: center; padding: 20px; color: #666; border-top: 1px solid #ddd; margin-top: 20px; }
          .highlight { color: #667eea; font-weight: bold; }
          .price { font-size: 1.2em; color: #28a745; font-weight: bold; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>🎉 Confirmation de Réservation</h1>
          <p>Votre billet pour ${booking.event.title}</p>
        </div>
        
        <div class="content">
          <p>Bonjour <strong>${booking.attendeeName}</strong>,</p>
          
          <p>Nous avons le plaisir de confirmer votre réservation pour l'événement suivant :</p>
          
          <div class="ticket-info">
            <h2>${booking.event.title}</h2>
            <p><strong>📍 Lieu:</strong> ${booking.event.venue}</p>
            <p><strong>📧 Adresse:</strong> ${booking.event.address}, ${booking.event.city}, ${booking.event.region || 'Cameroun'}</p>
            <p><strong>📅 Date:</strong> ${new Date(booking.event.eventDate).toLocaleDateString('fr-FR')}</p>
            <p><strong>🕐 Heure:</strong> ${booking.event.startTime}</p>
            <p><strong>🎫 Nombre de billets:</strong> ${booking.quantity}</p>
            <p><strong>💰 Total payé:</strong> <span class="price">${booking.totalAmount} ${booking.currency || 'XAF'}</span></p>
            <p><strong>🔢 Numéro de réservation:</strong> <span class="highlight">${booking.bookingReference}</span></p>
          </div>

          <div class="qr-code">
            <h3>Votre QR Code de Billet</h3>
            <p>Présentez ce QR code à l'entrée de l'événement :</p>
            <img src="${qrCodeDataURL}" alt="QR Code du billet" style="max-width: 200px; height: auto;" />
          </div>

          <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4>Instructions importantes :</h4>
            <ul>
              <li>Arrivez 15 minutes avant le début de l'événement</li>
              <li>Présentez ce QR code ou votre numéro de réservation à l'entrée</li>
              <li>Apportez une pièce d'identité valide</li>
              <li>Ce billet est personnel et non transférable</li>
            </ul>
          </div>

          <p>Nous avons hâte de vous voir à l'événement !</p>
          
          <p>Cordialement,<br>
          <strong>L'équipe EventZon</strong></p>
        </div>
        
        <div class="footer">
          <p>EventZon - Votre plateforme de réservation d'événements au Cameroun</p>
          <p>📧 support@eventzon.cm | 📞 +237 6XX XXX XXX</p>
        </div>
      </body>
      </html>
    `;

    const emailData: EmailData = {
      to: booking.attendeeEmail,
      subject: `Confirmation de réservation - ${booking.event.title}`,
      html: emailHtml,
    };

    await this.sendEmail(emailData);
  }

  static async sendEventReminder(
    booking: Booking & { event: Event },
    reminderType: '24h' | '2h'
  ): Promise<void> {
    const timeText = reminderType === '24h' ? '24 heures' : '2 heures';
    
    const emailHtml = `
      <!DOCTYPE html>
      <html lang="fr">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Rappel d'Événement - EventZon</title>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
          .header { background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
          .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
          .event-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ff6b6b; }
          .countdown { text-align: center; font-size: 1.5em; color: #ff6b6b; font-weight: bold; margin: 20px 0; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>⏰ Rappel d'Événement</h1>
          <p>Votre événement commence dans ${timeText} !</p>
        </div>
        
        <div class="content">
          <p>Bonjour <strong>${booking.attendeeName}</strong>,</p>
          
          <div class="countdown">
            Plus que ${timeText} avant votre événement !
          </div>
          
          <div class="event-info">
            <h2>${booking.event.title}</h2>
            <p><strong>📍 Lieu:</strong> ${booking.event.venue}</p>
            <p><strong>📧 Adresse:</strong> ${booking.event.address}, ${booking.event.city}</p>
            <p><strong>📅 Date:</strong> ${new Date(booking.event.eventDate).toLocaleDateString('fr-FR')}</p>
            <p><strong>🕐 Heure:</strong> ${booking.event.startTime}</p>
            <p><strong>🔢 Numéro de réservation:</strong> ${booking.bookingReference}</p>
          </div>

          <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <h4>N'oubliez pas :</h4>
            <ul>
              <li>Votre QR code ou numéro de réservation</li>
              <li>Une pièce d'identité valide</li>
              <li>D'arriver 15 minutes en avance</li>
            </ul>
          </div>

          <p>Nous avons hâte de vous voir !</p>
          
          <p>L'équipe EventZon</p>
        </div>
      </body>
      </html>
    `;

    const emailData: EmailData = {
      to: booking.attendeeEmail,
      subject: `Rappel : ${booking.event.title} commence dans ${timeText}`,
      html: emailHtml,
    };

    await this.sendEmail(emailData);
  }

  private static async sendEmail(emailData: EmailData): Promise<void> {
    try {
      const info = await this.transporter.sendMail({
        from: process.env.SMTP_FROM || '"EventZon" <noreply@eventzon.cm>',
        to: emailData.to,
        subject: emailData.subject,
        html: emailData.html,
        attachments: emailData.attachments,
      });

      console.log('Email sent successfully:', info.messageId);
    } catch (error) {
      console.error('Error sending email:', error);
      throw new Error('Failed to send email');
    }
  }
}