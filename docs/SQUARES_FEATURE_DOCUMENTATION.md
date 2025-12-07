# SQUARES POOL FEATURE DOCUMENTATION

> **Version:** 1.0
> **Last Updated:** November 29, 2025
> **Status:** Production

---

## TABLE OF CONTENTS

1. [Overview](#1-overview)
2. [System Architecture](#2-system-architecture)
3. [Database Schema](#3-database-schema)
4. [Core Features](#4-core-features)
5. [Business Logic](#5-business-logic)
6. [API Endpoints](#6-api-endpoints)
7. [Frontend Components](#7-frontend-components)
8. [User Flows](#8-user-flows)
9. [Configuration Options](#9-configuration-options)
10. [Email Notifications](#10-email-notifications)
11. [Known Limitations](#11-known-limitations)
12. [Future Enhancements](#12-future-enhancements)

---

## 1. OVERVIEW

### What is Squares Pool?

Squares Pool (also known as "Football Squares" or "Super Bowl Squares") is a popular sports betting game where:

- A 10x10 grid creates 100 squares
- Players purchase/claim squares on the grid
- Random numbers (0-9) are assigned to each row and column
- Winners are determined by matching the last digit of game scores at each quarter

### Key Terminology

| Term | Definition |
|------|------------|
| **Square** | One of 100 cells in the 10x10 grid |
| **Pool** | A single game/event with its own grid |
| **Commissioner** | Pool admin who creates and manages the pool |
| **Numbers Assignment** | Random or manual assignment of 0-9 to grid axes |
| **Quarter Winner** | Player whose square matches score digits at quarter end |
| **Entry Fee** | Cost per square (cash-based pools) |
| **Credits** | Virtual currency for credit-based pools |

---

## 2. SYSTEM ARCHITECTURE

### Technology Stack

```
Backend:
├── Laravel 10.x (PHP 8.1+)
├── MySQL 8.0
├── JWT Authentication
└── Clerk Integration (JWKS)

Frontend:
├── React 18.x
├── React Router v6
├── Clerk React SDK
├── Axios for API calls
└── PrimeReact UI Components
```

### File Structure

```
Backend (goatsportspools-backend):
├── app/
│   ├── Http/Controllers/
│   │   ├── SquaresPoolController.php      # Pool CRUD & management
│   │   └── SquaresPlayerController.php    # Player actions & claims
│   ├── Models/
│   │   ├── SquaresPool.php                # Pool model
│   │   ├── SquaresPoolSquare.php          # Individual square model
│   │   ├── SquaresPoolPlayer.php          # Player enrollment model
│   │   └── SquaresPoolWinner.php          # Winner records model
│   ├── Services/
│   │   ├── WinnerCalculationService.php   # Winner determination logic
│   │   └── QRCodeService.php              # QR code generation
│   └── Mail/
│       └── PoolClosedMail.php             # Pool closed notification
├── database/migrations/
│   ├── create_squares_pools_table.php
│   ├── create_squares_pool_squares_table.php
│   ├── create_squares_pool_players_table.php
│   └── create_squares_pool_winners_table.php
└── resources/views/emails/
    └── pool_closed.blade.php              # Email template

Frontend (goatsportspools/src/v2):
├── pages/
│   ├── SquaresPoolDetail.js               # Main pool view
│   ├── SquaresAdminDashboard.js           # Commissioner dashboard
│   └── CreateSquaresPool.js               # Pool creation form
├── components/squares/
│   ├── SquaresGrid.js                     # 10x10 grid component
│   ├── SquareCell.js                      # Individual cell
│   ├── WinnersDisplay.js                  # Quarter winners display
│   └── JoinPoolModal.js                   # Join pool interface
└── services/
    └── squaresApiService.js               # API client
```

---

## 3. DATABASE SCHEMA

### Entity Relationship Diagram

```
┌─────────────────┐       ┌──────────────────────┐
│     users       │       │    squares_pools     │
├─────────────────┤       ├──────────────────────┤
│ id (PK)         │◄──┐   │ id (PK)              │
│ name            │   │   │ admin_id (FK)────────┼──┐
│ email           │   │   │ game_id (FK)         │  │
│ role_id         │   │   │ pool_number (unique) │  │
│ balance         │   │   │ pool_name            │  │
└─────────────────┘   │   │ pool_type (A/B/C/D)  │  │
                      │   │ player_pool_type     │  │
                      │   │ x_numbers (JSON)     │  │
                      │   │ y_numbers (JSON)     │  │
                      │   │ numbers_assigned     │  │
                      │   │ entry_fee            │  │
                      │   │ credit_cost          │  │
                      │   │ initial_credits      │  │
                      │   │ max_squares_per_player│  │
                      │   │ reward1-4_percent    │  │
                      │   │ pool_status          │  │
                      │   └──────────────────────┘  │
                      │              │              │
                      │              │ 1:N          │
                      │              ▼              │
                      │   ┌──────────────────────┐  │
                      │   │ squares_pool_squares │  │
                      │   ├──────────────────────┤  │
                      │   │ id (PK)              │  │
                      │   │ pool_id (FK)─────────┼──┘
                      │   │ x_coordinate (0-9)   │
                      │   │ y_coordinate (0-9)   │
                      │   │ x_number (0-9)       │
                      │   │ y_number (0-9)       │
                      ├───┼─player_id (FK)       │
                      │   │ claimed_at           │
                      │   └──────────────────────┘
                      │              │
                      │              │ 1:N
                      │              ▼
                      │   ┌──────────────────────┐
                      │   │ squares_pool_players │
                      │   ├──────────────────────┤
                      │   │ id (PK)              │
                      │   │ pool_id (FK)         │
                      ├───┼─player_id (FK)       │
                      │   │ credits_available    │
                      │   │ squares_count        │
                      │   │ joined_at            │
                      │   └──────────────────────┘
                      │
                      │   ┌──────────────────────┐
                      │   │ squares_pool_winners │
                      │   ├──────────────────────┤
                      │   │ id (PK)              │
                      │   │ pool_id (FK)         │
                      │   │ square_id (FK)       │
                      └───┼─player_id (FK)       │
                          │ quarter (1-4)        │
                          │ prize_amount         │
                          │ home_score           │
                          │ visitor_score        │
                          │ payment_status       │
                          └──────────────────────┘
```

### Table Details

#### `squares_pools`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| admin_id | BIGINT | FK to users (pool creator) |
| game_id | BIGINT | FK to games table |
| pool_number | VARCHAR(6) | Unique 6-char identifier (e.g., "ABC123") |
| password | VARCHAR | Optional pool password (hashed) |
| pool_name | VARCHAR | Display name |
| pool_type | ENUM | A=Immediate, B=Auto, C=Manual, D=Winner/Loser |
| player_pool_type | ENUM | CASH or CREDIT |
| home_team_id | BIGINT | FK to teams |
| visitor_team_id | BIGINT | FK to teams |
| x_numbers | JSON | Array of 10 numbers for X-axis |
| y_numbers | JSON | Array of 10 numbers for Y-axis |
| numbers_assigned | BOOLEAN | Whether numbers have been assigned |
| entry_fee | DECIMAL(10,2) | Cost per square (CASH pools) |
| credit_cost | INT | Credits per square (CREDIT pools) |
| initial_credits | INT | Starting credits for new players |
| max_squares_per_player | INT | Limit per player (null = unlimited) |
| close_datetime | DATETIME | When pool closes for picks |
| number_assign_datetime | DATETIME | When numbers auto-assign (Type B) |
| pool_status | ENUM | open, closed, in_progress, completed |
| reward1_percent | DECIMAL | Q1 prize percentage |
| reward2_percent | DECIMAL | Q2 prize percentage |
| reward3_percent | DECIMAL | Q3 prize percentage |
| reward4_percent | DECIMAL | Q4/Final prize percentage |
| qr_code_url | VARCHAR | URL to pool QR code image |

#### `squares_pool_squares`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| pool_id | BIGINT | FK to squares_pools |
| x_coordinate | TINYINT | Column position (0-9) |
| y_coordinate | TINYINT | Row position (0-9) |
| x_number | TINYINT | Assigned number for X (0-9) |
| y_number | TINYINT | Assigned number for Y (0-9) |
| player_id | BIGINT | FK to users (null if unclaimed) |
| claimed_at | DATETIME | When square was claimed |

**Unique Constraint:** `(pool_id, x_coordinate, y_coordinate)`

#### `squares_pool_players`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| pool_id | BIGINT | FK to squares_pools |
| player_id | BIGINT | FK to users |
| credits_available | INT | Remaining credits |
| squares_count | INT | Number of squares claimed |
| joined_at | DATETIME | When player joined pool |

**Unique Constraint:** `(pool_id, player_id)`

#### `squares_pool_winners`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| pool_id | BIGINT | FK to squares_pools |
| square_id | BIGINT | FK to squares_pool_squares |
| player_id | BIGINT | FK to users |
| quarter | TINYINT | 1, 2, 3, or 4 |
| prize_amount | DECIMAL(10,2) | Amount won |
| home_score | INT | Home team score at quarter end |
| visitor_score | INT | Visitor team score at quarter end |
| payment_status | ENUM | pending, paid |

**Unique Constraint:** `(pool_id, quarter)` - One winner per quarter

---

## 4. CORE FEATURES

### 4.1 Grid Generation

When a pool is created, the system automatically generates 100 squares:

```php
// SquaresPoolController::store()
for ($y = 0; $y < 10; $y++) {
    for ($x = 0; $x < 10; $x++) {
        $squares[] = [
            'pool_id' => $pool->id,
            'x_coordinate' => $x,
            'y_coordinate' => $y,
        ];
    }
}
SquaresPoolSquare::insert($squares);
```

### 4.2 Number Assignment

Numbers (0-9) are assigned to each axis. Three methods:

**Type A - Immediate Random:**
```php
$xNumbers = collect(range(0, 9))->shuffle()->values()->toArray();
$yNumbers = collect(range(0, 9))->shuffle()->values()->toArray();
```

**Type B - Scheduled Auto:**
- Numbers assigned at `number_assign_datetime`
- Requires scheduled task (not yet automated)

**Type C - Manual:**
- Commissioner manually enters numbers
- Validated: must be 10 unique digits 0-9

### 4.3 Square Claiming

Players claim squares with these validations:

1. Pool must be "open" status
2. Square must be unclaimed
3. Player must not exceed `max_squares_per_player`
4. For CREDIT pools: player must have sufficient credits

```php
// SquaresPlayerController::claimSquare()
if ($pool->player_pool_type === 'CREDIT') {
    if ($playerRecord->credits_available < $pool->credit_cost) {
        return error('Insufficient credits');
    }
    $playerRecord->decrement('credits_available', $pool->credit_cost);
}

$square->update([
    'player_id' => $user->id,
    'claimed_at' => now(),
]);
```

### 4.4 Winner Determination

Winners are calculated using the **last digit** of quarterly scores:

```php
// WinnerCalculationService::calculateWinners()

// 1. Get quarter scores
$homeScore = $game->home_q1_score;    // e.g., 24
$visitorScore = $game->visitor_q1_score; // e.g., 17

// 2. Extract last digit
$homeLastDigit = $homeScore % 10;      // 4
$visitorLastDigit = $visitorScore % 10; // 7

// 3. Find winning coordinates
$xCoordinate = array_search($homeLastDigit, $pool->x_numbers);
$yCoordinate = array_search($visitorLastDigit, $pool->y_numbers);

// 4. Get winning square
$winningSquare = SquaresPoolSquare::where('pool_id', $pool->id)
    ->where('x_coordinate', $xCoordinate)
    ->where('y_coordinate', $yCoordinate)
    ->first();
```

**Visual Example:**

```
Pool Numbers:
X-axis (Home): [3, 7, 1, 9, 5, 0, 2, 8, 4, 6]
Y-axis (Visitor): [8, 2, 6, 0, 4, 9, 1, 5, 3, 7]

Q1 Score: Home 24, Visitor 17
Last digits: 4, 7

Find 4 in X-axis → position 8
Find 7 in Y-axis → position 9

Winner: Square at coordinates (8, 9)
```

### 4.5 Prize Distribution

Prizes are distributed by quarter based on configurable percentages:

```php
// Total Pot
$totalPot = $pool->entry_fee * $claimedSquaresCount;

// Prize per quarter
$q1Prize = $totalPot * ($pool->reward1_percent / 100);
$q2Prize = $totalPot * ($pool->reward2_percent / 100);
$q3Prize = $totalPot * ($pool->reward3_percent / 100);
$q4Prize = $totalPot * ($pool->reward4_percent / 100);
```

**Standard Distribution:**
- Q1: 20-25%
- Q2 (Halftime): 20-25%
- Q3: 20-25%
- Q4 (Final): 30-35%

**Validation:** reward1 + reward2 + reward3 + reward4 must equal 100%

---

## 5. BUSINESS LOGIC

### 5.1 Pool Status Lifecycle

```
┌─────────┐     Numbers      ┌─────────┐     Game       ┌─────────────┐     Game      ┌───────────┐
│  OPEN   │ ──────────────► │ CLOSED  │ ──────────────► │ IN_PROGRESS │ ─────────────► │ COMPLETED │
└─────────┘    Assigned      └─────────┘    Starts       └─────────────┘    Ends        └───────────┘
     │                            │
     │ All 100 squares            │
     │ claimed                    │
     └────────────────────────────┘
```

**Status Definitions:**

| Status | Description |
|--------|-------------|
| `open` | Accepting square claims |
| `closed` | Numbers assigned, no more claims |
| `in_progress` | Game is being played |
| `completed` | All winners determined |

### 5.2 Role Permissions

| Action | Superadmin (1) | Square Admin (2) | Player (3) |
|--------|----------------|------------------|------------|
| Create Pool | ✅ | ✅ | ❌ |
| View All Pools | ✅ | Own pools only | Joined pools only |
| Assign Numbers | ✅ | Own pools | ❌ |
| Add Player Credits | ✅ | Own pools | ❌ |
| Set Winners | ✅ | Own pools | ❌ |
| Join Pool | ✅ | ✅ | ✅ |
| Claim Squares | ✅ | ✅ | ✅ |

### 5.3 Credit System (CREDIT Pools)

```
Player joins pool
       │
       ▼
┌──────────────────────────┐
│ Receive initial_credits  │  (e.g., 10 credits)
└──────────────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Claim square             │
│ Cost: credit_cost        │  (e.g., 1 credit per square)
└──────────────────────────┘
       │
       ▼
┌──────────────────────────┐
│ credits_available -= 1   │
└──────────────────────────┘
       │
       ▼
   Need more credits?
       │
       ├── Yes ──► Request from Commissioner
       │           Commissioner approves
       │           credits_available += amount
       │
       └── No ──► Continue claiming
```

### 5.4 Multi-Select Square Claiming

Players can select multiple squares at once:

```javascript
// Frontend: SquaresGrid.js
const [selectedSquares, setSelectedSquares] = useState([]);

// Bulk claim endpoint
POST /api/squares-pools/{id}/claim-multiple
Body: { squares: [[0,1], [2,3], [4,5]] }
```

---

## 6. API ENDPOINTS

### Pool Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/squares-pools` | List pools (filtered by role) | Required |
| POST | `/api/squares-pools` | Create new pool | Admin+ |
| GET | `/api/squares-pools/{id}` | Get pool details | Required |
| PUT | `/api/squares-pools/{id}` | Update pool | Owner/Admin |
| DELETE | `/api/squares-pools/{id}` | Delete pool | Owner/Admin |

### Number Assignment

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/squares-pools/{id}/assign-numbers` | Auto-assign random numbers | Owner/Admin |
| POST | `/api/squares-pools/{id}/assign-numbers-manual` | Manual number assignment | Owner/Admin |

### Player Actions

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/squares-pools/{id}/join` | Join a pool | Required |
| POST | `/api/squares-pools/{id}/claim` | Claim single square | Required |
| POST | `/api/squares-pools/{id}/claim-multiple` | Claim multiple squares | Required |
| POST | `/api/squares-pools/{id}/unclaim` | Release a square | Required |
| GET | `/api/squares-pools/lookup/{poolNumber}` | Find pool by number | Required |

### Winner Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/squares-pools/{id}/winners` | Get pool winners | Required |
| POST | `/api/squares-pools/{id}/winners` | Set quarter winner | Owner/Admin |
| POST | `/api/squares-pools/{id}/calculate-winners` | Auto-calculate winners | Owner/Admin |

### Credit Management

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/squares-pools/{id}/players` | List pool players | Owner/Admin |
| POST | `/api/squares-pools/{id}/add-credits` | Add credits to player | Owner/Admin |

---

## 7. FRONTEND COMPONENTS

### 7.1 SquaresGrid Component

**Location:** `src/v2/components/squares/SquaresGrid.js`

**Props:**
```javascript
{
  pool: Object,           // Pool data with squares
  currentUser: Object,    // Logged-in user
  onSquareClick: Function, // Click handler
  selectedSquares: Array, // Currently selected
  teams: Object,          // Team data for headers
  highlightPlayer: Number // Player ID to highlight
}
```

**Features:**
- 10x10 grid rendering with team logos
- Color-coded squares:
  - Green: Your squares
  - Blue: Others' squares
  - Yellow: Selected for claim
  - White: Available
- Axis number display (after assignment)
- Multi-select mode
- Player highlight toggle

### 7.2 WinnersDisplay Component

**Location:** `src/v2/components/squares/WinnersDisplay.js`

**Features:**
- Quarter-by-quarter winner cards
- Score display with winning digits highlighted
- Player avatar and name
- Prize amount
- Payment status badge (Paid/Pending)
- Total prizes summary

### 7.3 SquaresPoolDetail Page

**Location:** `src/v2/pages/SquaresPoolDetail.js`

**Sections:**
1. Pool header (name, status, teams)
2. Pool stats (filled squares, pot, entry fee)
3. Admin controls (if authorized)
4. Squares grid
5. Winners display
6. Share/QR code section

### 7.4 Admin Controls

Available to pool owner and superadmins:

- **Assign Numbers:** Random or manual assignment
- **Set Winners:** Manual winner entry by quarter
- **Add Credits:** Grant credits to players
- **Lock Pool:** Close pool for claims
- **Share Pool:** QR code and link generation

---

## 8. USER FLOWS

### 8.1 Creating a Pool (Commissioner)

```
1. Navigate to /squares/create
2. Fill pool details:
   - Pool name
   - Select game/teams
   - Set pool type (A/B/C)
   - Set player type (CASH/CREDIT)
   - Set entry fee or credit cost
   - Set reward percentages
   - Set max squares per player (optional)
   - Set password (optional)
3. Submit → Pool created with 100 empty squares
4. Share pool link/QR code with players
```

### 8.2 Joining a Pool (Player)

```
1. Receive pool link or QR code
2. Click link or scan QR
3. If not logged in → Redirect to sign-in → Return to pool
4. Enter password if required
5. Click "Join Pool"
6. For CREDIT pools: Receive initial credits
7. Start claiming squares
```

### 8.3 Claiming Squares

```
1. View pool grid
2. Click available squares to select
3. Click "Claim Selected" button
4. For CREDIT pools: Credits deducted
5. Squares turn green (owned)
6. Repeat until satisfied or credits exhausted
```

### 8.4 Determining Winners

```
1. Commissioner assigns numbers (if not Type A)
2. All players receive email notification
3. Game is played
4. After each quarter:
   a. Get quarter score
   b. Extract last digits
   c. Find winning square
   d. Record winner and prize
5. After Q4: Pool marked completed
```

---

## 9. CONFIGURATION OPTIONS

### 9.1 Pool Types

| Type | Numbers Assignment | Use Case |
|------|-------------------|----------|
| A | Immediate random on creation | Quick games |
| B | Auto at scheduled datetime | Pre-planned events |
| C | Manual by commissioner | Full control |
| D | Winner/Loser based | Advanced (not implemented) |

### 9.2 Player Pool Types

| Type | Entry Method | Tracking |
|------|--------------|----------|
| CASH | Pay entry_fee per square | External payment |
| CREDIT | Use credits per square | In-app credits |

### 9.3 Environment Variables

```env
APP_FRONTEND_URL=https://example.com    # For email links
MAIL_MAILER=smtp                        # Email configuration
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="OKRNG Squares"
```

---

## 10. EMAIL NOTIFICATIONS

### 10.1 Pool Closed Notification

**Trigger:** When numbers are assigned (pool closes)

**Recipients:** All players who claimed squares

**Template:** `resources/views/emails/pool_closed.blade.php`

**Content:**
- Pool name and commissioner
- Player's square count
- Their number pairs (x,y) for each square
- Total squares filled
- Example winning scenario
- Link to view the pool

**Code Location:** `SquaresPoolController::sendPoolClosedEmails()`

---

## 11. KNOWN LIMITATIONS

### 11.1 Not Implemented

| Feature | Status | Notes |
|---------|--------|-------|
| Admin fee deduction | ❌ Missing | 100% pot goes to winners |
| Auto-close by datetime | ❌ Missing | Requires scheduled task |
| Auto-assign Type B | ❌ Missing | Requires scheduled task |
| Type D pool logic | ❌ Missing | Undefined business rules |
| Actual payment transfer | ❌ Missing | Winners recorded but no balance update |
| Refund mechanism | ❌ Missing | No cancellation flow |
| Live score integration | ❌ Missing | Manual score entry only |

### 11.2 Known Issues

1. **Role comparison:** Uses `==` instead of `===` to handle string/number coercion
2. **Unclaimed winners:** If winning square is unclaimed, no winner recorded for that quarter
3. **Same square multiple wins:** Same player can win multiple quarters (intended behavior)

---

## 12. FUTURE ENHANCEMENTS

### 12.1 High Priority

1. **Implement Admin Fee:**
```php
$adminFeePercent = $pool->admin_fee_percent ?? 10;
$netPot = $totalPot * (1 - $adminFeePercent / 100);
```

2. **Add Scheduled Tasks:**
```php
// app/Console/Kernel.php
$schedule->command('squares:process-pools')->everyMinute();
```

3. **Implement Payment Transfer:**
```php
$winner->player->increment('balance', $prizeAmount);
BalanceHistory::create([...]);
```

### 12.2 Medium Priority

4. Live score API integration
5. Push notifications for winners
6. Pool chat/comments
7. Historical statistics

### 12.3 Low Priority

8. Pool templates
9. Recurring pools
10. Tournament mode
11. Mobile app

---

## APPENDIX A: API Response Examples

### Get Pool Response

```json
{
  "status": true,
  "data": {
    "id": 1,
    "pool_number": "ABC123",
    "pool_name": "Super Bowl LVIII",
    "pool_type": "A",
    "player_pool_type": "CASH",
    "entry_fee": "10.00",
    "numbers_assigned": true,
    "x_numbers": [3, 7, 1, 9, 5, 0, 2, 8, 4, 6],
    "y_numbers": [8, 2, 6, 0, 4, 9, 1, 5, 3, 7],
    "pool_status": "open",
    "reward1_percent": "20.00",
    "reward2_percent": "20.00",
    "reward3_percent": "25.00",
    "reward4_percent": "35.00",
    "home_team": { "id": 1, "name": "Kansas City Chiefs" },
    "visitor_team": { "id": 2, "name": "San Francisco 49ers" },
    "squares": [...],
    "claimed_squares_count": 75,
    "available_squares_count": 25,
    "total_pot": "750.00"
  }
}
```

### Claim Square Response

```json
{
  "status": true,
  "message": "Square claimed successfully",
  "data": {
    "square": {
      "id": 45,
      "x_coordinate": 4,
      "y_coordinate": 5,
      "player_id": 123,
      "claimed_at": "2025-01-15T10:30:00Z"
    },
    "credits_remaining": 7
  }
}
```

---

## APPENDIX B: Database Migrations

See migration files in `database/migrations/`:
- `2024_xx_xx_create_squares_pools_table.php`
- `2024_xx_xx_create_squares_pool_squares_table.php`
- `2024_xx_xx_create_squares_pool_players_table.php`
- `2024_xx_xx_create_squares_pool_winners_table.php`

---

## APPENDIX C: Glossary

| Term | Definition |
|------|------------|
| **Axis Numbers** | The 0-9 digits assigned to grid rows/columns |
| **Claimed Square** | A square owned by a player |
| **Commissioner** | Pool administrator (role_id 2) |
| **Last Digit** | The ones place of a score (24 → 4) |
| **Pool Number** | Unique 6-character identifier |
| **Quarter Winner** | Player whose square matches score digits |
| **Superadmin** | Platform administrator (role_id 1) |

---

*Document maintained by the OKRNG Development Team*
