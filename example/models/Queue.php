<?php

    use Dez\ORM\Model\Table;

    class Queue extends Table {

        static $table = 'mail_queue';

        public function emailTo() {
            return $this->hasOne( Emails::class, 'id', 'to_email_id' );
        }

        public function emailFrom() {
            return $this->hasOne( Emails::class, 'id', 'from_email_id' );
        }

        public function subject() {
            return $this->hasOne( Subjects::class, 'id', 'subject_id' );
        }

        public function replacements() {
            return $this->hasMany( Replacements::class, 'mail_id' );
        }

        public function template() {
            return $this->hasOne( Temapltes::class, 'id', 'template_id' );
        }

        public static function addMail( $mail = [] ) {
//            die(var_dump( $mail ));
        }

        public static function addMailBatch( SimpleArrayCollection $mails ) {
            if( $mails->count() > 0 )
                foreach( $mails as $mail )
                    static::addMail( $mail );
        }

    }