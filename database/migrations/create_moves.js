exports.up = function (knex) {
    return knex.schema.createTable('moves', function (t) {
      t.increments('id').primary()
      t.integer('game_id').notNullable()
      t.string('played_by').notNullable()
      t.string('performed_at').notNullable()
      t.integer('coord_x').notNullable()
      t.integer('coord_y').notNullable()
    })
  };
  
  exports.down = function (knex) {
    return knex.schema.dropTableIfExists('moves')
  };