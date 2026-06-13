<style>
    /* Squadron Tables Professional Styling */
    .table-responsive {
        margin-bottom: 40px;
    }
    
    #squadronsTable, #membersTable, #leaderboardTable {
        background: rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(76, 175, 80, 0.3);
    }
    
    /* Squadron Headers */
    h2 {
        color: #4CAF50;
        font-size: 1.5rem;
        font-weight: 600;
        margin: 30px 0 20px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        padding-left: 20px;
    }
    
    h2:before {
        content: "▪";
        position: absolute;
        left: 0;
        color: #4CAF50;
    }
    
    /* Squadron toggle headers */
    .toggle-header {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.1) 0%, rgba(0, 0, 0, 0.3) 100%);
        border-bottom: 2px solid rgba(76, 175, 80, 0.3);
        transition: all 0.3s ease;
    }
    
    .toggle-header:hover {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(0, 0, 0, 0.4) 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
    }
    
    .toggle-header td:last-child {
        position: relative;
        padding-right: 40px;
    }
    
    .toggle-header td:last-child::after {
        content: "▼";
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #4CAF50;
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    
    .toggle-header.expanded td:last-child::after {
        transform: translateY(-50%) rotate(180deg);
    }
    
    /* Click to expand text styling */
    .toggle-header em {
        font-style: normal;
        color: #999;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .toggle-header:hover em {
        color: #4CAF50;
    }
    
    /* Squadron member rows */
    #membersTable tbody tr:not(.toggle-header) {
        background: rgba(0, 0, 0, 0.3);
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    #membersTable tbody tr:not(.toggle-header):hover {
        background: rgba(76, 175, 80, 0.05);
        border-left-color: #4CAF50;
        transform: translateX(3px);
    }
    
    /* Member name styling */
    .member-name a {
        display: inline-block;
        color: #e0e0e0;
        text-decoration: none;
        transition: color 0.2s ease;
        font-weight: 500;
    }
    
    .member-name a:hover {
        color: #4CAF50;
    }
    
    .member-name small {
        color: #777;
        font-size: 0.85rem;
        margin-left: 10px;
    }
    
    /* Squadron images */
    #squadronsTable img, #membersTable img, #leaderboardTable img {
        border: 1px solid rgba(76, 175, 80, 0.3);
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    #squadronsTable tr:hover img, 
    #membersTable tr:hover img, 
    #leaderboardTable tr:hover img {
        border-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
    }
    
    /* Leaderboard specific styling */
    #leaderboardTable tbody tr {
        background: rgba(0, 0, 0, 0.3);
        transition: all 0.2s ease;
    }
    
    #leaderboardTable tbody tr:hover {
        background: rgba(76, 175, 80, 0.05);
        transform: translateY(-1px);
    }
    
    /* Trophy medals with military rank feel */
    .medals {
        display: inline-block;
        width: 30px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        border-radius: 50%;
        font-size: 1.2rem;
        margin-right: 10px;
    }
    
    /* Squadron description */
    #squadronsTable td:last-child {
        color: #999;
        font-size: 0.95rem;
        line-height: 1.4;
    }
    
    /* Member count badge */
    .member-count {
        display: inline-block;
        background: rgba(76, 175, 80, 0.2);
        color: #4CAF50;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        margin-left: 10px;
    }
    
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        /* Remove fixed widths on mobile */
        #squadronsTable th,
        #squadronsTable td,
        #membersTable th,
        #membersTable td,
        #leaderboardTable th,
        #leaderboardTable td {
            width: auto !important;
        }
        
        /* Stack squadron info vertically on mobile */
        #squadronsTable td:first-child img {
            width: 50px !important;
            height: 50px !important;
            object-fit: cover;
        }
        
        #membersTable td:first-child img,
        #leaderboardTable td:first-child img {
            width: 40px !important;
            height: 40px !important;
            object-fit: cover;
        }
        
        /* Adjust font sizes */
        h2 {
            font-size: 1.2rem;
            margin: 20px 0 15px 0;
        }
        
        .dashboard-header h1 {
            font-size: 1.8rem;
        }
        
        .dashboard-subtitle {
            font-size: 0.9rem;
        }
        
        /* Make search container mobile-friendly */
        .search-container {
            padding: 0 15px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 100%;
        }
        
        #searchInput {
            width: 100%;
            max-width: 100%;
            padding: 12px 15px;
            font-size: 16px; /* Prevents zoom on iOS */
            box-sizing: border-box;
        }
        
        /* Adjust table text */
        table {
            font-size: 0.85rem;
        }
        
        /* Hide descriptions on very small screens */
        @media screen and (max-width: 480px) {
            #squadronsTable td:last-child {
                display: none;
            }
            
            #squadronsTable th:last-child {
                display: none;
            }
            
            .member-name small {
                display: block;
                margin-left: 0;
                margin-top: 5px;
            }
        }
        
        /* Improve toggle header on mobile */
        .toggle-header td {
            padding: 15px 10px;
        }
        
        .toggle-header em {
            font-size: 0.8rem;
        }
        
        /* Better member row styling on mobile */
        #membersTable tbody tr:not(.toggle-header) td {
            padding-left: 20px;
        }
    }
    
    /* Search container styling */
    .search-container {
        margin: 20px auto;
        max-width: 600px;
        text-align: center;
        box-sizing: border-box;
    }
    
    #searchInput {
        width: 100%;
        max-width: 100%;
        padding: 10px 15px;
        font-size: 1rem;
        background: rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(76, 175, 80, 0.3);
        color: #fff;
        border-radius: 25px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    
    #searchInput:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
    }
    
    #searchInput::placeholder {
        color: #999;
    }
    
    /* Mobile-specific squadron card styles */
    @media screen and (max-width: 768px) {
        /* Squadron members mobile card */
        .squadron-members-card {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .squadron-members-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            cursor: pointer;
            position: relative;
        }
        
        .squadron-members-info {
            flex: 1;
            min-width: 0;
        }
        
        .squadron-members-count {
            font-size: 0.85rem;
            color: #999;
            margin-top: 3px;
        }
        
        .expand-indicator {
            font-size: 1.2rem;
            color: #4CAF50;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }
        
        .squadron-members-list {
            border-top: 1px solid rgba(76, 175, 80, 0.2);
            max-height: 300px;
            overflow-y: auto;
        }
        
        .member-item {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .member-item:active {
            background: rgba(76, 175, 80, 0.1);
        }
        
        .member-name {
            color: #fff;
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .member-date {
            font-size: 0.8rem;
            color: #999;
        }
        
        /* Leaderboard mobile card */
        .leaderboard-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 15px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 12px;
        }
        
        .leaderboard-card-rank {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4CAF50;
            min-width: 50px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .leaderboard-card-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }
        
        .leaderboard-card-info {
            flex: 1;
            min-width: 0;
        }
        
        .squadron-credits {
            font-size: 0.9rem;
            color: #999;
            margin-top: 3px;
        }
        
        /* Ensure squadron logos don't overflow */
        .squadron-card-logo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(76, 175, 80, 0.3);
            flex-shrink: 0;
        }
        
        /* Fix squadron name overflow */
        .squadron-card-name {
            font-size: 1.1rem;
            color: #4CAF50;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .squadron-placeholder {
            background: rgba(76, 175, 80, 0.1);
            border: 1px dashed rgba(76, 175, 80, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            font-size: 24px;
            color: rgba(76, 175, 80, 0.5);
        }

    }
</style>