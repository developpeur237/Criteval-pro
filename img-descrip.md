# Formation module layout redesign

## Reference

The annotated reference image shows the desired visual hierarchy for the Formation module page. The arrows indicate that the existing dashboard cards and panels should be repositioned and reorganised into a clearer operational layout.

## Layout requirements

### 1. Page header

- Keep the `Formation` title and subtitle at the top-left.
- Keep the session selector at the top-right as the primary context control.
- Keep the `Exporter` action beside the selector.
- Keep the `Nouvelle formation` action as the primary green action on the far right.

### 2. Summary metric cards

Place the four summary cards in one horizontal row directly below the header:

1. Formations planifiées.
2. Participantes / participants inscrits.
3. Taux de présence.
4. Moyenne des acquis /20.

Each card must have equal width, consistent height, aligned content, and enough contrast for the icon, number, label, and accent indicator.

### 3. Main content area

Below the summary row, use a two-column layout:

- Left column: the catalogue of formations.
- Right column: the pilotage panel for the currently selected session.

The left catalogue should be the wider column, approximately 58–62% of the available width. The right pilotage column should use the remaining 38–42%.

### 4. Catalogue de formations

The catalogue occupies the large left panel indicated by the green annotation.

- Display the section title and its explanatory subtitle at the top.
- Keep the table columns aligned:
  - Formation
  - Organisation
  - Dates
  - Participants
  - Statut
  - Action
- Keep each formation row compact and readable.
- Keep the eye/view action aligned in the final column.
- Long formation names and objectives must wrap without overflowing the panel.
- The catalogue should scroll internally when there are many sessions, while the page layout remains stable.

### 5. Pilotage de la session

The pilotage panel occupies the upper-right area indicated by the purple annotation.

- Show the selected organisation/session context and active status.
- Display the session metrics in a compact two-column grid:
  - Présence
  - Poids optionnel
  - Présents
  - Moyenne
- Keep the `Notes incluses dans les évaluations` setting below those metrics.
- Keep the `Enregistrer les changements` and `Voir impact` actions together at the bottom of the panel.
- The panel must remain visible while the catalogue is scrolled when practical.

### 6. Criteria / evaluation list panel

The lower-right area indicated by the orange annotation is reserved for a training criteria list.

- Add a dedicated panel titled `Training Criteria list` or an equivalent French label.
- Show the criteria associated with the selected organisation, evaluation, or training session.
- Each criterion should be presented as a readable list item with its label and, where available, description or completion state.
- The panel must support scrolling independently when the criteria list is long.
- The criteria panel must update when the selected session changes.

### 7. Visual and interaction rules

- Preserve the existing dark dashboard visual language.
- Use consistent spacing, borders, rounded corners, and accent colours across all panels.
- Keep the green accent for active/positive actions and statuses.
- Keep the right-side panels visually distinct from the catalogue without making them overpower the page.
- Ensure the layout is responsive:
  - Desktop: two-column catalogue/pilotage layout.
  - Tablet: reduced-width two-column layout where possible.
  - Mobile: stack catalogue, pilotage, and criteria panels vertically.
- Do not remove existing formation management, attendance, evaluation-weight, export, or session-selection functionality.

## Scope of implementation after approval

When approved, update the Formation module view, its CSS/layout rules, and any required JavaScript so that:

1. The boxes and panels follow the positions shown by the annotated arrows.
2. The catalogue, pilotage panel, and criteria list use the correct data sources.
3. Session selection updates all dependent panels.
4. Existing create, view, attendance, save, and export actions remain operational.
5. The result is tested at desktop and narrow viewport sizes.
