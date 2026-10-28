<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class ProductInquiry extends Mailable
{
 use Queueable,SerializesModels;
 public function __construct(public array $inquiry) {}
 public function envelope(): Envelope { return new Envelope(replyTo:[new Address($this->inquiry['email'],$this->inquiry['name'])],subject:'Nowe zapytanie — Home Kreator'); }
 public function content(): Content { return new Content(view:'mail.inquiry'); }
}
