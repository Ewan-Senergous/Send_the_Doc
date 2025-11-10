# Quick Push avec Gitmoji

Exécute un workflow git complet : add, diff, commit avec gitmoji, et push vers main.

## Étapes

1. **Stage tous les fichiers :**

   ```bash
   git add -A
   ```

2. **Affiche résumé des changements :**

   ```bash
   git diff --cached --stat
   ```

3. **Analyse les changements et propose le bon gitmoji :**
   - `:bug:` - Corrections de bugs
   - `:sparkles:` - Nouvelles fonctionnalités
   - `:recycle:` - Refactoring de code
   - `:fire:` - Suppression de code/fichiers
   - `:art:` - Amélioration structure/format
   - `:lipstick:` - Mise à jour UI/styles
   - `:zap:` - Amélioration performance
   - `:memo:` - Mise à jour documentation
   - Autres gitmojis selon contexte

4. **Crée un commit avec message < 72 caractères (SANS signature Claude Code)**

5. **Push vers main :**
   ```bash
   git push origin main
   ```

## Instructions

- **UN SEUL commit** pour tous les changements du `git add -A` (pas de commits multiples)
- Demande à l'utilisateur de confirmer ou modifier le message de commit proposé
- Offre option d'afficher `git diff --cached` (diff complet) si besoin
- Message format : `<emoji> <description courte>` (max 72 caractères)
- IMPORTANT : Aucune signature "Generated with Claude Code" ou similaire

## Exemples de Bonnes Pratiques

**Corrections de bugs :**

```
:bug: fix login validation error
:bug: correct database connection timeout
```

**Nouvelles fonctionnalités :**

```
:sparkles: add CSV export for products
:sparkles: implement dark mode toggle
```

**Refactoring :**

```
:recycle: simplify authentication flow
:recycle: extract reusable table component
```

**Suppressions :**

```
:fire: remove deprecated API endpoints
:fire: delete unused utility functions
```

**Modifications UI/Styles :**

```
:lipstick: update button styles
:art: improve code formatting
:zap: optimize table rendering performance
```

**Changements mixtes (choisir l'action principale) :**

```
:sparkles: add export feature with bug fixes
:recycle: refactor auth with new validation
:bug: fix multiple UI issues
```
