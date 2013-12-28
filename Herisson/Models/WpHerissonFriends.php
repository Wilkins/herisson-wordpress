<?php
/**
 * WpHerissonFriends
 * 
 * @category Models
 * @package  Herisson
 * @author   Thibault Taillandier <thibault@taillandier.name>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @link     None
 */

/**
 * ORM class to handle Friend object
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @category Models
 * @package  Herisson
 * @author   Thibault Taillandier <thibault@taillandier.name>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @link     None
 */
class WpHerissonFriends extends BaseWpHerissonFriends
{

    public function getInfo()
    {
        $url = $this->url."/info";
        $network = new HerissonNetwork();
        try  {
            $json_data = $network->download($url);
            $data = json_decode($json_data['data'], 1);

            if (sizeof($data)) {
                $this->name  = $data['sitename'];
                $this->email = $data['adminEmail'];
            } else {
                $this->is_active=0;
            }

        } catch (HerissonNetworkException $e) {
            $this->is_active=0;
            switch ($e->getCode()) {
            case 404:
                HerissonMessage::i()->addError(__("This site is not a Herisson site or is closed.", HERISSON_TD));
                break;
            }
        }
    }

    public function setUrl($url)
    {
        parent::_set('url', rtrim($url, '/'));
        $this->reloadPublicKey();
    }

    public function reloadPublicKey()
    {
        $network = new HerissonNetwork();
        try {

            $content = $network->download($this->url."/publickey");
            $this->_set('public_key', $content['data']);

        } catch (HerissonNetworkException $e) {
            switch ($e->getCode()) {
            case 404:
                HerissonMessage::i()->addError(__("This site is not a Herisson site or is closed.", HERISSON_TD));
                break;
            }
        }
    }


    public function retrieveBookmarks($params=array())
    {
        
        $options = get_option('HerissonOptions');
        $my_public_key = $options['publicKey'];
        if (function_exists('curl_init')) {
            $network = new HerissonNetwork();
            $params['key'] = $my_public_key;
            try {

                $content = $network->download($this->url."/retrieve", $params);
                $encryption_data = json_decode($content['data'], true);
                $json_data = HerissonEncryption::i()->privateDecryptLongData($encryption_data['data'], $encryption_data['hash'], $encryption_data['iv']);
                $bookmarks = json_decode($json_data, 1);
                return $bookmarks;

            } catch (HerissonNetworkException $e) {
                switch ($e->getCode()) {
                case 404:
                    HerissonMessage::i()->addError(__("This site is not a Herisson site or is closed.", HERISSON_TD));
                    break;
                }
            }
        }
    }

    public function generateBookmarksData($params=array())
    {
        $options = get_option('HerissonOptions');
        $my_private_key = $options['privateKey'];
        $q = Doctrine_Query::create()
            ->from('WpHerissonBookmarks as b')
            ->where('is_public=1')
            ;
        if (array_key_exists('tag', $params)) {
            $q = $q->leftJoin('b.WpHerissonTags t');
            $q = $q->where("t.name=?");
            $params = array($params['tag']);
        } else if (array_key_exists('search', $params)) {
            $search = "%".$params['search']."%";
            $q = $q->leftJoin('b.WpHerissonTags t');
            $q = $q->where("t.name LIKE ? OR b.url like ? OR b.title LIKE ? OR b.description LIKE ? OR b.content LIKE ?");
            $params = array($search, $search, $search, $search, $search);
        }
        $bookmarks = $q->execute($params);

        $data_bookmarks = array();
        foreach ($bookmarks as $bookmark) {
            $data_bookmarks[] = $bookmark->toArray();
        }
        $json_data = json_encode($data_bookmarks);
        try {
            $json_display = HerissonEncryption::i()->publicEncryptLongData($json_data, $this->public_key);
        } catch (HerissonEncryptionException $e) {
            HerissonNetwork::reply(417);
            echo $e->getMessage();
        }
        return json_encode($json_display);
    }

    public function askForFriend()
    {
        $options    = get_option('HerissonOptions');
        $url        = $this->url."/ask";
        $mysite     = get_option('siteurl')."/".$options['basePath'];
        $signature  = HerissonEncryption::i()->privateEncrypt($mysite);
        $postData = array(
            'url'       => $mysite,
            'signature' => $signature
        );
        $network = new HerissonNetwork();
        try {
            $content = $network->download($url, $postData);
            switch ($content['code']) {
            case 200: 
                // Friend need to process the request manually, you will be notified when validated.
                $this->b_youwant=1;
                $this->save();
                break;
            case 202:
                // Friend automatically accepted the request. Adding now.
                $this->is_active=1;
                $this->save();
                break;
            }
        } catch (HerissonNetworkException $e) {
            switch ($e->getCode()) {
            case 403:
                HerissonMessage::i()->addError(__("This site refuses new friends.", HERISSON_TD));
                break;
            case 404:
                HerissonMessage::i()->addError(__("This site is not a Herisson site or is closed.", HERISSON_TD));
                break;
            case 417:
                HerissonMessage::i()->addError(__("Friend say you dont communicate correctly (key problems?).", HERISSON_TD));
                break;
                
            }
        }
    }

    public function validateFriend()
    {
        $signature = HerissonEncryption::i()->privateEncrypt(HERISSON_LOCAL_URL);
        $postData = array(
            'url'       => HERISSON_LOCAL_URL,
            'signature' => $signature
        );
        $network = new HerissonNetwork();
        try {
            $content = $network->download($this->url."/validate", $postData);
            if ($content['data'] === "1") {
                $this->b_wantsyou=0;
                $this->is_active=1;
                $this->save();
                return true;
            } else {
                return false;
            }
        } catch (HerissonNetworkException $e) {
            HerissonMessage::i()->addError($e->getMessage());
            return false;
        }
    }

}