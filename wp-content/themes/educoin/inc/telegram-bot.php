<?php

    /**
      * To create instance just do: $telegram_bot = new Telegram_Bot( '<bot_token>' );
      *
      */
    class Telegram_Bot {
    
        private $bot_token;
        private $api_endpoint = 'https://api.telegram.org/bot<token>';
      
        public function __construct( $bot_token ) {
            
            $this->bot_token = $bot_token;
            $this->api_endpoint = str_replace( '<token>', $bot_token, $this->api_endpoint );
        }

        /**
          * Prepare message
          * 
          * @param   $text        string      Text of the message
          * 
          * @return  string
          */
        protected function prepare_message( string $text ) {

            // need to do multiple entity decode conversion because of the lumauts
            $text = html_entity_decode( $text );
            $text = html_entity_decode( $text );

            return $text;
        }

        /**
          * Send message
          * 
          * @param   $chat_id     string      Chat ID to send the message
          * @param   $text        string      Text of the message
          * @param   $parse_mode  string      HTML or Markdown
          * 
          * @return  array
          */
        public function send_message( string $chat_id, string $text, string $parse_mode = '' ) {

            // prepare text message
            $text = $this->prepare_message( $text );

            // adding the user
            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => $parse_mode,
            ];

            $endpoint_after_main_endpoint = "sendMessage";
            $request_type = 'GET';

            // send the data
            $result = $this->make_curl_request( $endpoint_after_main_endpoint, $request_type, $data );

            return $result;
        }

        /**
          * Get chat
          * 
          * @param   $chat_id     string      Chat ID
          * 
          * @return  array
          */
        public function get_chat( string $chat_id ) {

            $data = [
                'chat_id' => $chat_id,
            ];

            $endpoint_after_main_endpoint = "getChat";
            $request_type = 'GET';

            // send the data
            $result = $this->make_curl_request( $endpoint_after_main_endpoint, $request_type, $data );

            return $result;
        }

        /**
          * Get updates
          * 
          * @return  array
          */
        public function get_updates( $query_data = [] ) {

            $data = [];
            
            if ( isset( $query_data['offset'] ) ) {
                $data['offset'] = $query_data['offset'];
            }
            // should we return just last message?
            if ( isset( $query_data['is_return_last_msg'] ) && $query_data['is_return_last_msg'] ) {
                $data['offset'] = -1;
            }
            
            $endpoint_after_main_endpoint = "getUpdates";
            $request_type = 'GET';

            // send the data
            $result = $this->make_curl_request( $endpoint_after_main_endpoint, $request_type, $data );

            return $result;
        }

        /**
          * Make curl request. All requests is made like GET requests.
          * 
          * $endpoint_after_main_endpoint       string           Will be added to basic endpoint
          * $data                               assoc_array      Data to send
          * 
          * @return                             array
          */
        private function make_curl_request( $endpoint_after_main_endpoint, $request_type, $data = [] ) {

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, "{$this->api_endpoint}/{$endpoint_after_main_endpoint}" );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $request_type ); 
            curl_setopt( $ch, CURLOPT_POST, true ); 
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data ); 

            $result = curl_exec( $ch );
            curl_close( $ch );

            return json_decode( $result );
        }
        
    }
?>