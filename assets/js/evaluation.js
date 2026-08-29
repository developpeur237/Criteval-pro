(function () {
  window.CritevalEvaluation = window.CritevalEvaluation || {
    weightedScore: function (items) {
      return (items || []).reduce(function (total, item) {
        return total + (Number(item.score || 0) * Number(item.weight || 1));
      }, 0);
    }
  };
})();
