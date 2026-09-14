<?php

namespace App\Mail;

use App\Models\EmbalsesRan;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrevisionDesembalse extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmbalsesRan $embalse,
        public string|int|float $caudal,
        public CarbonInterface $fechaPrevista,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Previsión desembalse %s - %s', $this->embalse->er_codigo, $this->embalse->er_nombre),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.desembalse',
        );
    }
}
