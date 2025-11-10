# Ultra Think - Méthodologie Rigoureuse de Développement

Approche systématique en 5 phases pour garantir qualité et fiabilité du code.

## 🔍 Phase 1 : COMPRENDRE

**Objectif :** Clarifier les besoins avant toute action

1. **Lire la demande attentivement**
2. **Identifier les zones floues** → Poser questions utilisateur
3. **Confirmer compréhension** si demande ambiguë

## 🔎 Phase 2 : EXPLORER

**Objectif :** Chercher informations pertinentes dans le code et sur le web

### Recherche Code et Web

- **Recherches larges** : Agent Explore (Task tool) pour questions ouvertes
  ```
  Task tool avec subagent_type="Explore" pour :
  - "Comment fonctionne X ?"
  - "Où est géré Y ?"
  - "Structure du module Z ?"
  ```
- **Recherches ciblées** : Outil `Grep` (pas bash grep)
- **Lecture fichiers** : Outil `Read` (intégralité pour contexte)
- **Fichiers par pattern** : Outil `Glob`
- **Recherche Web** : Utiliser WebSearch/WebFetch si besoin pendant exploration
  - Comprendre une API/bibliothèque
  - Trouver bonnes pratiques pour pattern spécifique
  - Vérifier compatibilité versions

## 📋 Phase 3 : PLANIFIER

**Objectif :** Créer stratégie détaillée AVANT de coder

1. **Utiliser TodoWrite** si tâche complexe (≥3 étapes)
   - Décomposer en sous-tâches actionables
   - Une tâche in_progress à la fois
2. **Identifier fichiers à modifier**
3. **Lister dépendances et impacts**
4. **⚠️ STOP et DEMANDER** si :
   - Architecture incertaine
   - Plusieurs approches possibles
   - Impact sur autres modules flou
5. **Attendre validation utilisateur** avant coder

## 💻 Phase 4 : CODER

**Objectif :** Implémenter avec qualité et précision

### Principes

- ✅ **Modifier uniquement nécessaire** (pas de refactoring non demandé)
- ✅ **Préférer Edit à Write** pour fichiers existants
- ✅ **Chemins Windows absolus** : `C:\Users\...\file.js`
- ✅ Si erreur "File modified" → `pnpm format` puis relire

### Bonnes Pratiques TypeScript

- ❌ Éviter `any` → `unknown`, `Record<string, unknown>`
- ✅ Types spécifiques ou interfaces
- ✅ Typage strict pour maintenabilité

### Anti-Hardcoding

- ❌ Pas de valeurs DB hardcodées
- ✅ Utiliser Prisma DMMF : `getTableMetadata()`, `getAllTables()`
- ✅ Config UI centralisée si nécessaire

### Svelte 5

- ✅ `$state` pour variables réactives
- ✅ `$derived` pour valeurs calculées
- ✅ `$effect` pour effets de bord
- ✅ Clés dans `{#each}` : `{#each items as item (item.id)}`

### Gestion Erreurs

- ✅ Toasts avec `svelte-sonner`
- ✅ Validation avec schémas Zod

### 🔄 Si Bloqué Pendant Implémentation

- **Après 2-3 tentatives d'erreurs** → WebSearch obligatoire
- Ne pas s'acharner sur solution qui ne marche pas
- Chercher vraie solution plutôt que variantes hasardeuses

## ✅ Phase 5 : TESTER

**Objectif :** Vérifier qualité et fonctionnement

### Qualité Code (OBLIGATOIRE)

```bash
/quality-check  # Lint + Format + Type check
```

### Validation

- ✅ Tous les tests passent → Succès
- ❌ Tests échouent → **RETOUR Phase 3 PLANIFIER**
  - Analyser cause échec
  - Ajuster plan
  - Recoder proprement

### TodoWrite

- ✅ Marquer tâche completed immédiatement après succès
- ✅ Une tâche à la fois

## 📝 Workflow Complet Exemple

```
1. COMPRENDRE → "Ajouter export CSV kits"
2. EXPLORER → Grep "export", Read components, Agent Explore structure, WebSearch si besoin
3. PLANIFIER → TodoWrite (3 tâches), confirmer approche utilisateur
4. CODER → Edit composants existants, chemins absolus, types stricts
           Si erreur répétée 3x → WebSearch
5. TESTER → /quality-check passe, mark completed
```

---

**Principe d'or :** Think → Plan → Code → Test → Iterate
