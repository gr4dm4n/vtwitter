<?php
/**
 * VIANCH Twitter Class
 *
 * @author Victor Chavarro {@link http://www.vianch.com Victor Chavarro (victor@vianch.com)}
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
define("TWITTER_TIMELINE_URL", "https://api.twitter.com/1/statuses/user_timeline.json"); //url del request del timeline, retorna json con la información
define("TWITTER_AVATAR_URL", "http://twitter.com/api/users/profile_image/");
define("CACHE_TIMEOUT", 60*5); //tiempo de cacheo de la base de datos, es cada 5 minutos 

class vtwitter{
	
	/*Propiedades para el funcionamiento de la aplicación*/
	private $vt_properties; 

	/**
	 * Instancia la clase e inicializa la variable de propiedades que se van a usar en las funciones
	 * @param $properties propiedades de la aplicación
	 */
	public function __construct( $properties = null ){
		
		if( $properties != null ){
		
			$this->vt_properties = array(
				'title' => $properties['title'],
				'screen_name' => preg_replace( '/[^A-Za-z0-9_]/', '', $properties['screen_name'] ),
				'count' =>  $properties['count'],
				'published_when' => ( isset( $properties['published_when'] ) ? 1 : 0 ), 
			);
		
		}
		else{
			echo "ERROR: Lo se sefinieron los parámetros";
		}

	}

	/**
	 * Realiza la conexión con twitter a traves de una URL, retorna el JSON con la información
	 * o falso si no se puede realizar la consulta
     * @return mixed
	 */ 

	private function twitter_connect(){
		$twitter_url = TWITTER_TIMELINE_URL."?screen_name=".$this->vt_properties['screen_name'];
		$twitter_connect= curl_init(); //inicializacion CURL
		$timeout = 7; //sesión abierta por una hora

		/*PARAMETROS CURL*/
		curl_setopt($twitter_connect,CURLOPT_URL, $twitter_url); //Dirección URL a capturar
		curl_setopt($twitter_connect,CURLOPT_RETURNTRANSFER,1); //para devolver el resultado de la transferencia como string
		curl_setopt($twitter_connect,CURLOPT_CONNECTTIMEOUT,$timeout); //Número de segundos a esperar cuando se está intentado conectar.
		
		$twitter_data = curl_exec($twitter_connect); //se conecta a la url
		
		curl_close($twitter_connect); //cierra la conexión
		
		//si no se logra obtener datos retorna falso, de lo contrario retorne la información	
		if( ( !$twitter_data ) || ( $twitter_data === FALSE) ){
			return false;
		}
		else{
			return $twitter_data;
		}
	}

	/**
	 * Genera la lista en html de la cantidad de tweets que se especificaron en los parámetros,
	 * retorna un string con el html formateado
	 * @see vtwitter_printer();
	 * @return string;
	 */
	private function generate_tweet_list(){
		
		$tweets_info = $this->twitter_connect();  //se obtiene la información de los últimos 20 tweets
		$list = '<ul>'; //lista de tweets ya formateados en html, $list es el string que retorna la función
		$user_avatar = TWITTER_AVATAR_URL.$this->vt_properties['screen_name']; //avatar del usuario
		$user_name = $this->vt_properties['screen_name'];

		$tweets_info = json_decode($tweets_info);
	
		if( !is_array($tweets_info) || count($tweets_info) < 2 ){
			
				$list .= '<li>No hay tweets disponibles</li>'; //si no se puede obtener la información de los tweets
			
		}
		else{

			$contador = 1;
			foreach($tweets_info AS $tweet){
				$tweet_text = $tweet->text;
				$list .= "<li><img src='$user_avatar' alt='$user_name' /><span><b> $user_name</b>: $tweet_text  </span> ";

				if( $this->vt_properties['published_when'] == 1 ){
                    $list .= "<span class='time-meta'>";
                        $time_diff = 'hace '.$this->human_time_diff( strtotime($tweet->created_at));
                        $list .= "$time_diff";
                    $list .= '</span>';
                }
				
				$list .= "</li>";
				++$contador;
				
				if($contador > $this->vt_properties['count']){
					break;
				}
			}
			
		}

		$list .= "</ul>";

		return $list;
	}


	/**
	 * imprime el html formateado de los tweets
	 * @see generate_tweet_list;
	 */
	public function vtwitter_printer(){
		echo $this->generate_tweet_list();
	}

    /**
     * Determines the difference between two timestamps.
	 * Función tomada del core de wordpress para el funcionamiento fuera de el
     */
	public function human_time_diff( $from, $to = '' ) {
		        if ( empty($to) )
		                $to = time();
		        $diff = (int) abs($to - $from);
		        if ($diff <= 3600) {
		                $mins = round($diff / 60);
		                if ($mins <= 1) {
		                        $mins = 1;
		                }
		                /* translators: min=minute */
		                $since = sprintf('%s min', $mins);
		        } else if (($diff <= 86400) && ($diff > 3600)) {
		                $hours = round($diff / 3600);
		                if ($hours <= 1) {
		                        $hours = 1;
		                }
		                if($hours > 1){
		                	 $since = sprintf('%s horas', $hours);
		                }
		                else{
		                	 $since = sprintf('%s houra', $hours);
		                }
		               
		        } elseif ($diff >= 86400) {
		                $days = round($diff / 86400);
		                if ($days <= 1) {
		                        $days = 1;
		                }

		        }
		        return $since;
	}



	/**
	 * resetea el array de propiedades
	 */
	public function __destruct(){
		$this->vt_properties = array();
	}
}

?>