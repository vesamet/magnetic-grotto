
exports.seed = function (knex, Promise) {
  // Deletes ALL existing entries
  return knex('players').del()
    .then(function () {
      // Inserts seed entries
      return knex('players').insert([
        {
          id: null,
          name: "marc",
          wins: 0,
          defeats: 0,
          current_game: null,
          player_role: 1
        },
        {
          id: null,
          name: "justine",
          wins: 2,
          defeats: 0,
          current_game: null,
          player_role: 1
        },
        {
          id: null,
          name: "jean",
          wins: 3,
          defeats: 1,
          current_game: null,
          player_role: 1
        }
      ]);
    });
};