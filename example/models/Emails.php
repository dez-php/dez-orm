<?php

    use Dez\ORM\Model\Table;

    class Emails extends Table {

        static $table = 'mail_address';

        public function queue() {
            return $this->hasOne( Queue::class, 'from_email_id' );
        }

    }