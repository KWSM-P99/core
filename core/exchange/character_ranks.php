<?php
if (!defined('EQDKP_INC')){
    die('Do not access this file directly.');
}

if (!class_exists('exchange_character_ranks')){
    /**
     * Class exchange_character_ranks
     * Handles the retrieval of character ranks for an exchange system.
     */
    class exchange_character_ranks extends gen_class{
        // Shortcuts for dependencies
        public static $shortcuts = array('pex'=>'plus_exchange');

        /**
         * Retrieves character ranks if the request is authenticated via API token.
         *
         * @param array $params Parameters for the request (not used in this function).
         * @param array $arrBody Body of the request (not used in this function).
         * @return array Returns an array of character ranks or an error message.
         */
        public function get_character_ranks($params, $arrBody){
            // Check if the request is authenticated via API token
            $isAPITokenRequest = $this->pex->getIsApiTokenRequest();
            if ($isAPITokenRequest){
                // Retrieve a list of character IDs
                $arrCharacters = $this->pdh->get('member', 'id_list', array(false, false, false));
                $arrCharacterRanks = array();

                // Iterate over each character ID to fetch their rank details
                foreach ($arrCharacters as $charID) {
                    $charName = $this->pdh->get('member', 'name', array($charID)); // Get character name
                    $rankID = $this->pdh->get('member', 'rankid', array($charID)); // Get rank ID
                    $rankName = $this->pdh->get('rank', 'name', array($rankID)); // Get rank name

                    // Append character rank details to the result array
                    $arrCharacterRanks[] = array(
                        'character_id' => $charID,
                        'character_name' => $charName,
                        'rank_id' => $rankID,
                        'rank_name' => $rankName,
                    );
                }

                // Return the array of character ranks
                return array('characters' => $arrCharacterRanks);
            } else {
                // Return an error message if access is denied
                return $this->pex->error('access denied');
            }
        }
    }
} 