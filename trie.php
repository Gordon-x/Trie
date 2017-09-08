<?php

class Trie
{
    public $next = [];
    public $node = '';
    public $count = 0;
}

class Dict
{
    /**
     * @var Trie
     */
    public $trie = null;
    /**
     * @var SplStack
     */
    public $stack = null;
    /**
     * @var Trie[]
     */
    public $findTrie = [];

    public function __construct()
    {
        $this->trie = new Trie();
        $this->stack = new SplStack();
        $this->str = clone $this->stack;
    }

    public function insert($str)
    {
        if (empty($str)) {
            return;
        }
        $char = $str{0};
        $key = ord($char);
        if (isset($this->trie->next[$key])
            && $this->trie->next[$key] instanceof Trie
        ) {
            $this->trie->next[$key]->count += 1;
        } else {
            $this->trie->next[$key] = new Trie();
            $this->trie->next[$key]->count = 1;
            $this->trie->next[$key]->node = $char;
        }
        $str = substr($str, 1);
        $this->stack->push($this->trie);
        $this->trie = $this->trie->next[$key];
        if (!empty($str)) {

            $this->insert($str);
        }
        $p = $this->stack->pop();
        $p->next[$key] = $this->trie;
        $this->trie = $p;
    }
    
    public function searchCount($name, $str)
    {
        $trie = file_get_contents($name);
        $this->trie = unserialize($trie);
        $str = strtolower($str);
        $start = $str{0};

        $result = [];
        $tmpWords = [];
        $this->findCharStart($start);


        foreach ($this->findTrie as $trie) {
            $tmp = $trie;
            for ($i = 1; $i < strlen($str); $i++) {
                $char = $str{$i};
                $key = ord($char);
                if (isset($tmp->next[$key])) {
                    $tmp = $tmp->next[$key];
                    if ($i == strlen($str) - 1) {
                        $result[$str] = $tmp->count;
                        $tmpWords[] = $trie;
                    }
                } else {
                    break;
                }
            }
        }

        $words = [];
        foreach ($tmpWords as $k => $word) {
            $words[$k] .= $word->node;
        }

        return [$result, $words];
    }

    /**
     * @param $char
     */
    public function findCharStart($char)
    {
        $key = ord($char);
        if (isset($this->trie->next[$key])) {
            $this->findTrie[] = $this->trie->next[$key];
        }

        foreach ($this->trie->next as $trie) {
            $this->stack->push($this->trie);
            $this->trie = $trie;
            $this->findCharStart($char);
            $tmp = $this->stack->pop();
            $this->trie = $tmp;
        }
    }

    public function createDict($name, $str)
    {
        preg_match_all("/(?<=\b)([a-zA-Z]+)(?=\b)/", $str, $matches);

        $words = $matches[1];

        foreach ($words as $word) {
            $word = strtolower($word);
            $this->insert($word);
        }
        file_put_contents($name, serialize($this->trie));
    }
}

$a = new Dict();

$str = "The authorization code is obtained by using an authorization server
   as an intermediary between the client and resource owner.  Instead of
   requesting authorization directly from the resource owner, the client
   directs the resource owner to an authorization server (via its
   user-agent as defined in [RFC2616]), which in turn directs the
   resource owner back to the client with the authorization code.

   Before directing the resource owner back to the client with the
   authorization code, the authorization server authenticates the
   resource owner and obtains authorization.  Because the resource owner
   only authenticates with the authorization server, the resource
   owner's credentials are never shared with the client.

   The authorization code provides a few important security benefits,
   such as the ability to authenticate the client, as well as the
   transmission of the access token directly to the client without
   passing it through the resource owner's user-agent and potentially
   exposing it to others, including the resource owner.";

var_dump($a->searchCount('tree.txt', 'auth'));
echo "<pre>";
echo "</pre>";

