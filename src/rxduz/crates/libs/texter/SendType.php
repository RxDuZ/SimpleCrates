<?php

namespace rxduz\crates\libs\texter;

enum SendType: string
{
    case ADD = "add";
    case EDIT = "edit";
    case MOVE = "move";
    case REMOVE = "remove";
}
