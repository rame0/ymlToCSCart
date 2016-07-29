;<?php /* !!! DO NOT REMOVE THIS LINE !!! */ die(); ?>


[login]

USE_PASS                = YES
USER_LOGIN              = "user"
USER_PASS              = "password"

[default_mapping]

;Product Code
field[0] = "attr//id"
;Product id
field[2] = "tag//vendorCode"
;Category
field[3] = "build_Cat_Name"
;Price
field[5] = 'tag//price'
;Product name
field[19] = 'tag//name'
;Status
field[34] = 'attr//available'

[features_types]

feature[C] = "Single"
feature[M] = "Multiple"
feature[S] = "Text"
feature[N] = "Number"
feature[E] = "Brand/Manufacturer"
feature[T] = "Text"
feature[O] = "Number"
feature[D] = "Date"