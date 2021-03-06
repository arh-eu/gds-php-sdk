<?php
/*
 * Copyright 2020 ARH Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Gds\Message\MessagePack;

use App\Gds\Message\Message;
use App\Gds\Message\MessageData;
use MessagePack\Packer;
use MessagePack\TypeTransformer\Packable;

/**
 * @author bordacs
 */
class MessageTransformer implements Packable
{
    public function pack(Packer $packer, $value) : ?string
    {
        if (!($value instanceof Message)) {
            return null;
        }

        $data = $value->getData();
        if (!($data instanceof MessageData)) {
            throw new \LogicException('Only MessageData can be packed!');
        }

        $header = $value->getHeader();
        $fragmentation = $header->getFragmentation();

        return $packer->pack(array(
            $header->getUserName(),
            $header->getMessageId(),
            $header->getCreateTime(),
            $header->getRequestTime(),
            $fragmentation->isFragmented(),
            $fragmentation->isFirstFragment(),
            $fragmentation->isLastFragment(),
            $fragmentation->offset(),
            $fragmentation->fullDataSize(),
            $data->getType(),
            $data->getData()
        ));
    }
}
