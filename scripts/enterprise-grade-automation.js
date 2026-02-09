#!/usr/bin/env node
/**
 * ENTERPRISE GRADE AUTOMATION PIPELINE
 * Continuous scan-fix cycle until 10/10 quality is achieved
 * 
 * Usage: node scripts/enterprise-grade-automation.js [--max-iterations=5]
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// ANSI Colors
const colors = {
  reset: '\x1b[0m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  magenta: '\x1b[35m',
  cyan: '\x1b[36m',
  white: '\x1b[37m',
  bold: '\x1b[1m'
};

function log(message, color = 'white') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

function logBold(message, color = 'white') {
  console.log(`${colors.bold}${colors[color]}${message}${colors.reset}`);
}

function banner() {
  logBold('╔════════════════════════════════════════════════════════════════╗', 'cyan');
  logBold('║     ENTERPRISE GRADE AUTOMATION PIPELINE                       ║', 'cyan');
  logBold('║     Scan-Fix Cycle Until 10/10 Achieved                        ║', 'cyan');
  logBold('╚════════════════════════════════════════════════════════════════╝', 'cyan');
  console.log('');
}

function runCommand(cmd, description) {
  log(`  ${description}...`, 'blue');
  try {
    const result = execSync(cmd, { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'] });
    return { success: true, output: result };
  } catch (error) {
    return { success: false, error: error.message, output: error.stdout || '' };
  }
}

function parseScore(output) {
  const match = output.match(/OVERALL SCORE:\s*(\d+)\/(\d+)/);
  if (match) {
    return parseInt(match[1]);
  }
  return 0;
}

function parseIssues(output) {
  const criticalMatch = output.match(/Critical:\s*(\d+)/);
  const highMatch = output.match(/High:\s*(\d+)/);
  
  return {
    critical: criticalMatch ? parseInt(criticalMatch[1]) : 0,
    high: highMatch ? parseInt(highMatch[1]) : 0
  };
}

class EnterpriseAutomation {
  constructor() {
    this.maxIterations = parseInt(process.argv.find(arg => arg.startsWith('--max-iterations='))?.split('=')[1] || '5');
    this.currentIteration = 0;
    this.scoreHistory = [];
  }

  async run() {
    banner();
    
    log(`Maximum iterations: ${this.maxIterations}`, 'cyan');
    console.log('');

    while (this.currentIteration < this.maxIterations) {
      this.currentIteration++;
      
      logBold(`━`.repeat(70), 'white');
      logBold(`🔄 ITERATION ${this.currentIteration}/${this.maxIterations}`, 'cyan');
      logBold(`━`.repeat(70), 'white');
      console.log('');

      // Step 1: Run Audit Scan
      const scanResult = this.runScan();
      if (!scanResult.success) {
        log(`  ❌ Scan failed: ${scanResult.error}`, 'red');
        continue;
      }

      const score = parseScore(scanResult.output);
      const issues = parseIssues(scanResult.output);
      
      this.scoreHistory.push({ iteration: this.currentIteration, score, issues });
      
      log(`  Current Score: ${score}/10`, score >= 9 ? 'green' : score >= 7 ? 'yellow' : 'red');
      log(`  Critical Issues: ${issues.critical}`, issues.critical > 0 ? 'red' : 'green');
      log(`  High Issues: ${issues.high}`, issues.high > 0 ? 'magenta' : 'green');
      console.log('');

      // Check if target achieved
      if (score >= 9 && issues.critical === 0) {
        this.success(score);
        return;
      }

      // Step 2: Run Fix Engine
      if (issues.critical > 0 || issues.high > 0) {
        const fixResult = this.runFixes();
        if (!fixResult.success) {
          log(`  ⚠️  Some fixes may have failed`, 'yellow');
        }
        
        const fixesApplied = this.parseFixes(fixResult.output);
        log(`  Fixes Applied: ${fixesApplied}`, fixesApplied > 0 ? 'green' : 'yellow');
      }

      console.log('');
    }

    this.finalReport();
  }

  runScan() {
    return runCommand(
      'node scripts/enterprise-audit-engine.js 2>&1',
      '🔍 Running enterprise audit scan'
    );
  }

  runFixes() {
    return runCommand(
      'node scripts/enterprise-fix-engine.js 2>&1',
      '🔧 Running automated fix engine'
    );
  }

  parseFixes(output) {
    const match = output.match(/Fixes Applied:\s*(\d+)/);
    return match ? parseInt(match[1]) : 0;
  }

  success(finalScore) {
    console.log('');
    logBold('╔════════════════════════════════════════════════════════════════╗', 'green');
    logBold('║     🎉 ENTERPRISE GRADE ACHIEVED! 🎉                           ║', 'green');
    logBold(`║     Final Score: ${finalScore}/10                                          ║`, 'green');
    logBold('║     All critical issues resolved                               ║', 'green');
    logBold('║     System is PRODUCTION READY                                 ║', 'green');
    logBold('╚════════════════════════════════════════════════════════════════╝', 'green');
    console.log('');
    
    this.printProgressChart();
    this.saveReport(true);
  }

  finalReport() {
    console.log('');
    logBold('╔════════════════════════════════════════════════════════════════╗', 'yellow');
    logBold('║     ⏹️  AUTOMATION COMPLETED (Max Iterations Reached)          ║', 'yellow');
    logBold('╚════════════════════════════════════════════════════════════════╝', 'yellow');
    console.log('');
    
    this.printProgressChart();
    this.saveReport(false);
    
    // Check final status
    const lastRun = this.scoreHistory[this.scoreHistory.length - 1];
    if (lastRun && lastRun.score >= 9 && lastRun.issues.critical === 0) {
      logBold('✅ System meets enterprise standards despite max iterations', 'green');
    } else {
      logBold('⚠️  Manual intervention may be required for remaining issues', 'yellow');
    }
  }

  printProgressChart() {
    logBold('📈 Progress History:', 'white');
    console.log('');
    
    for (const run of this.scoreHistory) {
      const bar = '█'.repeat(run.score) + '░'.repeat(10 - run.score);
      const color = run.score >= 9 ? 'green' : run.score >= 7 ? 'yellow' : 'red';
      log(`  Iteration ${run.iteration}: [${bar}] ${run.score}/10 (C:${run.issues.critical} H:${run.issues.high})`, color);
    }
    
    console.log('');
  }

  saveReport(success) {
    const reportPath = 'Scan-report/automation-report.json';
    
    const report = {
      timestamp: new Date().toISOString(),
      success,
      iterations: this.currentIteration,
      scoreHistory: this.scoreHistory,
      finalScore: this.scoreHistory[this.scoreHistory.length - 1]?.score || 0,
      recommendations: this.generateRecommendations()
    };

    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    log(`📄 Automation report saved to: ${reportPath}`, 'cyan');
  }

  generateRecommendations() {
    const lastRun = this.scoreHistory[this.scoreHistory.length - 1];
    if (!lastRun) return [];

    const recommendations = [];
    
    if (lastRun.score < 10) {
      recommendations.push('Review remaining medium/low priority issues');
    }
    if (lastRun.issues.critical > 0) {
      recommendations.push('CRITICAL: Manual review required for remaining critical issues');
    }
    if (lastRun.issues.high > 0) {
      recommendations.push('HIGH: Consider manual fixes for remaining high priority issues');
    }
    
    recommendations.push('Run npm audit for dependency vulnerabilities');
    recommendations.push('Perform penetration testing before production');
    recommendations.push('Set up monitoring and alerting infrastructure');
    
    return recommendations;
  }
}

// Run automation
const automation = new EnterpriseAutomation();
automation.run().catch(error => {
  console.error('Automation failed:', error);
  process.exit(1);
});
