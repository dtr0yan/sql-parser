<?php

namespace SqlParser\Fragments;

use SqlParser\Fragment;
use SqlParser\Lexer;
use SqlParser\Parser;
use SqlParser\Token;
use SqlParser\TokensList;

/**
 * `JOIN` keyword parser.
 */
class JoinKeyword extends Fragment
{

    /**
     * Join expression.
     *
     * @var FieldFragment
     */
    public $expr;

    /**
     * Join conditions.
     *
     * @var WhereKeyword
     */
    public $on;

    /**
     * @param Parser $parser
     * @param TokensList $list
     * @param array $options
     *
     * @return JoinKeyword
     */
    public static function parse(Parser $parser, TokensList $list, array $options = array())
    {
        $ret = new JoinKeyword();

        /**
         * The state of the parser.
         *
         * Below are the states of the parser.
         *
         *      0 -----------------------[ expr ]----------------------> 1
         *
         *      1 ------------------------[ ON ]-----------------------> 2
         *
         *      2 --------------------[ conditions ]-------------------> -1
         *
         * @var int
         */
        $state = 0;

        for (; $list->idx < $list->count; ++$list->idx) {
            /** @var Token Token parsed at this moment. */
            $token = $list->tokens[$list->idx];

            // End of statement.
            if ($token->type === Token::TYPE_DELIMITER) {
                break;
            }

            // Skipping whitespaces and comments.
            if (($token->type === Token::TYPE_WHITESPACE) || ($token->type === Token::TYPE_COMMENT)) {
                continue;
            }

            if ($state === 0) {
                $ret->expr = FieldFragment::parse($parser, $list, array('skipColumn' => true));
                $state = 1;
            } elseif ($state === 1) {
                if (($token->type === Token::TYPE_KEYWORD) && ($token->value === 'ON')) {
                    $state = 2;
                }
            } elseif ($state === 2) {
                $ret->on = WhereKeyword::parse($parser, $list);
                ++$list->idx;
                break;
            }

        }

        --$list->idx;
        return $ret;
    }
}