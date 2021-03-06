<?php

namespace FanFerret\QuestionBundle\Utility;

/**
 * Contains utilities for working with the PHP
 * DateTime type.
 */
class DateTime
{
    /**
     * Returns a copy of the provided DateTime object which
     * has had its associated DateTimeZone changed to UTC.
     *
     * @param DateTime|null $dt
     *
     * @return DateTime|null
     */
    public static function toDoctrine(\DateTime $dt = null)
    {
		if (is_null($dt)) return null;
        $retr = clone $dt;
        $retr->setTimezone(new \DateTimeZone('UTC'));
        return $retr;
    }

    /**
     * Returns a a DateTime object (which may or may not be a
     * copy depending on whether any operation was necessary)
     * which has been compensated for any oddities introduced by
     * persistence to and retrieval from Doctrine.
     *
     * @param DateTime|null $dt
     *
     * @return DateTime|null
     */
    public static function fromDoctrine(\DateTime $dt = null)
    {
		if (is_null($dt)) return null;
        if ($dt->getTimezone()->getName() === 'UTC') return $dt;
        $fmt = 'Y-m-d H:i:s';
        return \DateTime::createFromFormat($fmt,$dt->format($fmt),new \DateTimeZone('UTC'));
    }
}
