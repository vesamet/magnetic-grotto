exports.up = function (knex) {
    return knex.schema.createTable('players', function (t) {
      t.increments('id').primary()
      t.string('name').notNullable()
      t.integer('wins').notNullable()
      t.integer('defeats').notNullable()
      t.integer('current_game').nullable()
      t.string('created_at').notNullable()
    })
  };
  
  exports.down = function (knex) {
    return knex.schema.dropTableIfExists('players')
  };